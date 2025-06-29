<?php

namespace App\Observers;

use App\Models\Document;
use App\Jobs\ProcessDocumentContent;
use App\Jobs\CleanupDocumentFiles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * Document Observer
 * 
 * Handles model events for Document model including automatic processing,
 * cleanup, caching, and logging of document lifecycle events.
 */
class DocumentObserver
{
    /**
     * Handle the Document "creating" event.
     */
    public function creating(Document $document): void
    {
        // Set default values if not provided
        if (empty($document->title) && !empty($document->original_filename)) {
            $document->title = pathinfo($document->original_filename, PATHINFO_FILENAME);
        }

        // Generate file hash if not set
        if (empty($document->file_hash) && !empty($document->file_path)) {
            $document->file_hash = $this->generateFileHash($document);
        }

        // Log document creation attempt
        Log::info('Document creation started', [
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
            'file_size' => $document->file_size,
            'mime_type' => $document->mime_type,
        ]);
    }

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        // Clear user's document count cache
        $this->clearUserCache($document->user_id);

        // Dispatch processing job for uploaded documents
        if ($document->status === Document::STATUS_UPLOADED) {
            ProcessDocumentContent::dispatch($document)
                ->delay(now()->addSeconds(5)); // Small delay to ensure transaction is committed
        }

        // Update user statistics
        $this->updateUserStatistics($document->user_id);

        // Log successful creation
        Log::info('Document created successfully', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
            'status' => $document->status,
        ]);

        // Track analytics if enabled
        if (config('cat-simplifier.analytics.enabled')) {
            $this->trackDocumentEvent('document_created', $document);
        }
    }

    /**
     * Handle the Document "updating" event.
     */
    public function updating(Document $document): void
    {
        // Log status changes
        if ($document->isDirty('status')) {
            $oldStatus = $document->getOriginal('status');
            $newStatus = $document->status;

            Log::info('Document status changing', [
                'document_id' => $document->id,
                'user_id' => $document->user_id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Handle specific status transitions
            $this->handleStatusTransition($document, $oldStatus, $newStatus);
        }

        // Update processed_at timestamp when marking as completed
        if ($document->status === Document::STATUS_COMPLETED && empty($document->processed_at)) {
            $document->processed_at = now();
        }
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        // Clear relevant caches
        $this->clearDocumentCache($document);
        $this->clearUserCache($document->user_id);

        // Handle completed documents
        if ($document->status === Document::STATUS_COMPLETED && $document->wasChanged('status')) {
            $this->handleDocumentCompletion($document);
        }

        // Handle failed documents
        if ($document->status === Document::STATUS_FAILED && $document->wasChanged('status')) {
            $this->handleDocumentFailure($document);
        }

        // Update user statistics if relevant fields changed
        if ($document->wasChanged(['status', 'file_size'])) {
            $this->updateUserStatistics($document->user_id);
        }

        // Track analytics for status changes
        if (config('cat-simplifier.analytics.enabled') && $document->wasChanged('status')) {
            $this->trackDocumentEvent('document_status_changed', $document, [
                'old_status' => $document->getOriginal('status'),
                'new_status' => $document->status,
            ]);
        }
    }

    /**
     * Handle the Document "deleting" event.
     */
    public function deleting(Document $document): void
    {
        Log::info('Document deletion started', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
            'status' => $document->status,
        ]);

        // Cancel any pending processing jobs for this document
        $this->cancelPendingJobs($document);

        // Schedule file cleanup (with delay to ensure transaction completes)
        if (!empty($document->file_path)) {
            CleanupDocumentFiles::dispatch($document->file_path)
                ->delay(now()->addMinutes(5));
        }
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        // Clear all related caches
        $this->clearDocumentCache($document);
        $this->clearUserCache($document->user_id);

        // Update user statistics
        $this->updateUserStatistics($document->user_id);

        // Log successful deletion
        Log::info('Document deleted successfully', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
        ]);

        // Track analytics
        if (config('cat-simplifier.analytics.enabled')) {
            $this->trackDocumentEvent('document_deleted', $document);
        }
    }

    /**
     * Handle the Document "forceDeleted" event.
     */
    public function forceDeleted(Document $document): void
    {
        // Immediately delete the physical file
        if (!empty($document->file_path) && Storage::exists($document->file_path)) {
            Storage::delete($document->file_path);
        }

        // Clear all caches
        $this->clearDocumentCache($document);
        $this->clearUserCache($document->user_id);

        Log::info('Document force deleted', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
        ]);
    }

    /**
     * Handle the Document "restored" event.
     */
    public function restored(Document $document): void
    {
        // Clear caches
        $this->clearDocumentCache($document);
        $this->clearUserCache($document->user_id);

        // Update user statistics
        $this->updateUserStatistics($document->user_id);

        // Restart processing if the document was uploaded but not processed
        if ($document->status === Document::STATUS_UPLOADED && empty($document->extracted_content)) {
            ProcessDocumentContent::dispatch($document)
                ->delay(now()->addSeconds(10));
        }

        Log::info('Document restored', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
            'status' => $document->status,
        ]);

        // Track analytics
        if (config('cat-simplifier.analytics.enabled')) {
            $this->trackDocumentEvent('document_restored', $document);
        }
    }

    /**
     * Generate file hash for the document.
     */
    protected function generateFileHash(Document $document): ?string
    {
        try {
            if (!empty($document->file_path) && Storage::exists($document->file_path)) {
                $filePath = Storage::path($document->file_path);
                return hash_file('sha256', $filePath);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to generate file hash', [
                'document_id' => $document->id ?? 'new',
                'file_path' => $document->file_path,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    /**
     * Handle status transitions with specific logic.
     */
    protected function handleStatusTransition(Document $document, ?string $oldStatus, string $newStatus): void
    {
        switch ($newStatus) {
            case Document::STATUS_PROCESSING:
                $this->handleProcessingStart($document);
                break;

            case Document::STATUS_COMPLETED:
                if ($oldStatus === Document::STATUS_PROCESSING) {
                    $this->handleProcessingComplete($document);
                }
                break;

            case Document::STATUS_FAILED:
                if ($oldStatus === Document::STATUS_PROCESSING) {
                    $this->handleProcessingFailed($document);
                }
                break;

            case Document::STATUS_ARCHIVED:
                $this->handleDocumentArchival($document);
                break;
        }
    }

    /**
     * Handle when document processing starts.
     */
    protected function handleProcessingStart(Document $document): void
    {
        // Clear any previous processing error
        if (!empty($document->processing_error)) {
            $document->processing_error = null;
        }

        // Set processing start time in cache for monitoring
        Cache::put("document_processing_start_{$document->id}", now(), 3600);

        Log::info('Document processing started', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'filename' => $document->original_filename,
        ]);
    }

    /**
     * Handle when document processing completes successfully.
     */
    protected function handleProcessingComplete(Document $document): void
    {
        // Calculate processing time
        $startTime = Cache::get("document_processing_start_{$document->id}");
        if ($startTime) {
            $processingTime = now()->diffInSeconds($startTime);
            Cache::forget("document_processing_start_{$document->id}");

            // Store processing metrics
            $this->storeProcessingMetrics($document, $processingTime, true);
        }

        // Trigger notifications if enabled
        if (config('cat-simplifier.notifications.email.enabled')) {
            $this->scheduleCompletionNotification($document);
        }

        Log::info('Document processing completed', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'processing_time' => $processingTime ?? null,
            'content_length' => strlen($document->extracted_content ?? ''),
        ]);
    }

    /**
     * Handle when document processing fails.
     */
    protected function handleProcessingFailed(Document $document): void
    {
        // Calculate processing time
        $startTime = Cache::get("document_processing_start_{$document->id}");
        if ($startTime) {
            $processingTime = now()->diffInSeconds($startTime);
            Cache::forget("document_processing_start_{$document->id}");

            // Store processing metrics
            $this->storeProcessingMetrics($document, $processingTime, false);
        }

        // Trigger failure notifications if enabled
        if (config('cat-simplifier.notifications.email.enabled')) {
            $this->scheduleFailureNotification($document);
        }

        Log::warning('Document processing failed', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'error' => $document->processing_error,
            'processing_time' => $processingTime ?? null,
        ]);
    }

    /**
     * Handle document completion (called from updated event).
     */
    protected function handleDocumentCompletion(Document $document): void
    {
        // Check if auto-simplification is enabled for the user
        if ($this->shouldAutoCreateSimplification($document)) {
            $this->scheduleAutoSimplification($document);
        }

        // Update document statistics
        $this->updateDocumentStatistics($document);
    }

    /**
     * Handle document failure (called from updated event).
     */
    protected function handleDocumentFailure(Document $document): void
    {
        // Increment failure counter for monitoring
        $failureKey = "document_failures_user_{$document->user_id}";
        $currentFailures = Cache::get($failureKey, 0);
        Cache::put($failureKey, $currentFailures + 1, 3600); // 1 hour

        // Alert if user has too many failures
        if ($currentFailures >= 5) {
            Log::alert('User has multiple document processing failures', [
                'user_id' => $document->user_id,
                'failure_count' => $currentFailures + 1,
            ]);
        }
    }

    /**
     * Handle document archival.
     */
    protected function handleDocumentArchival(Document $document): void
    {
        // Schedule file cleanup after archival period
        $cleanupDelay = config('cat-simplifier.processing.cleanup.processed_documents_archive_days', 90);
        
        CleanupDocumentFiles::dispatch($document->file_path)
            ->delay(now()->addDays($cleanupDelay));

        Log::info('Document archived', [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'cleanup_scheduled_for' => now()->addDays($cleanupDelay)->toDateString(),
        ]);
    }

    /**
     * Cancel any pending jobs for the document.
     */
    protected function cancelPendingJobs(Document $document): void
    {
        // In a real implementation, you would cancel jobs from the queue
        // This is a simplified approach using cache flags
        
        $jobKeys = [
            "cancel_document_processing_{$document->id}",
            "cancel_document_jobs_{$document->id}",
        ];

        foreach ($jobKeys as $key) {
            Cache::put($key, true, 300); // 5 minutes
        }
    }

    /**
     * Clear document-related cache entries.
     */
    protected function clearDocumentCache(Document $document): void
    {
        $cacheKeys = [
            "document_stats_{$document->id}",
            "document_content_{$document->id}",
            "document_processing_start_{$document->id}",
            "user_documents_{$document->user_id}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear user-related cache entries.
     */
    protected function clearUserCache(int $userId): void
    {
        $cacheKeys = [
            "user_stats_{$userId}",
            "user_documents_{$userId}",
            "user_document_count_{$userId}",
            "user_total_file_size_{$userId}",
            "dashboard_stats_{$userId}",
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Update user statistics in cache.
     */
    protected function updateUserStatistics(int $userId): void
    {
        // Recalculate and cache user statistics
        $user = \App\Models\User::find($userId);
        if ($user) {
            $stats = $user->getDashboardStats();
            Cache::put("user_stats_{$userId}", $stats, config('cat-simplifier.cache.user_stats_ttl', 3600));
        }
    }

    /**
     * Store processing metrics for analytics.
     */
    protected function storeProcessingMetrics(Document $document, int $processingTime, bool $success): void
    {
        if (!config('cat-simplifier.analytics.track_processing_metrics')) {
            return;
        }

        $metrics = [
            'document_id' => $document->id,
            'user_id' => $document->user_id,
            'file_type' => $document->getFileExtension(),
            'file_size' => $document->file_size,
            'processing_time' => $processingTime,
            'success' => $success,
            'timestamp' => now(),
        ];

        // Store in cache for batch processing
        $key = 'processing_metrics_' . now()->format('Y-m-d-H');
        $existing = Cache::get($key, []);
        $existing[] = $metrics;
        Cache::put($key, $existing, 86400); // 24 hours
    }

    /**
     * Track document events for analytics.
     */
    protected function trackDocumentEvent(string $event, Document $document, array $additionalData = []): void
    {
        if (!config('cat-simplifier.analytics.track_user_actions')) {
            return;
        }

        $eventData = array_merge([
            'event' => $event,
            'document_id' => $document->id,
            'user_id' => config('cat-simplifier.analytics.anonymize_user_data') ? 
                hash('sha256', $document->user_id) : $document->user_id,
            'file_type' => $document->getFileExtension(),
            'file_size' => $document->file_size,
            'timestamp' => now(),
        ], $additionalData);

        // Store in cache for batch processing
        $key = 'analytics_events_' . now()->format('Y-m-d-H');
        $existing = Cache::get($key, []);
        $existing[] = $eventData;
        Cache::put($key, $existing, 86400); // 24 hours
    }

    /**
     * Check if auto-simplification should be created.
     */
    protected function shouldAutoCreateSimplification(Document $document): bool
    {
        // This could be a user preference or system setting
        return false; // Disabled by default - users should manually create simplifications
    }

    /**
     * Schedule auto-simplification creation.
     */
    protected function scheduleAutoSimplification(Document $document): void
    {
        // Implementation would dispatch a job to create a default simplification
        // This is optional functionality
    }

    /**
     * Schedule completion notification.
     */
    protected function scheduleCompletionNotification(Document $document): void
    {
        // Dispatch notification job
        \App\Jobs\SendDocumentProcessedNotification::dispatch($document)
            ->delay(now()->addMinutes(1));
    }

    /**
     * Schedule failure notification.
     */
    protected function scheduleFailureNotification(Document $document): void
    {
        // Dispatch notification job
        \App\Jobs\SendDocumentFailedNotification::dispatch($document)
            ->delay(now()->addMinutes(1));
    }

    /**
     * Update document-specific statistics.
     */
    protected function updateDocumentStatistics(Document $document): void
    {
        if (!$document->content_statistics && $document->extracted_content) {
            $stats = [
                'word_count' => str_word_count($document->extracted_content),
                'character_count' => strlen($document->extracted_content),
                'line_count' => substr_count($document->extracted_content, "\n") + 1,
                'paragraph_count' => count(array_filter(explode("\n\n", $document->extracted_content))),
            ];

            $document->update(['content_statistics' => $stats]);
        }
    }
}