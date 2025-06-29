<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\DocumentParsingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process Document Content Job
 * 
 * Background job that extracts text content from uploaded documents
 * and prepares them for AI simplification processing.
 */
class ProcessDocumentContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes
    public $tries = 3;
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {
        // Set queue based on file size (larger files to slower queue)
        $this->onQueue($document->file_size > 5 * 1024 * 1024 ? 'heavy' : 'default');
    }

    /**
     * Execute the job.
     */
    public function handle(DocumentParsingService $parsingService): void
    {
        Log::info('Starting document content processing', [
            'document_id' => $this->document->id,
            'filename' => $this->document->original_filename,
            'file_size' => $this->document->file_size,
        ]);

        try {
            // Check if document still exists and is in correct state
            if (!$this->document->canBeProcessed()) {
                Log::warning('Document cannot be processed', [
                    'document_id' => $this->document->id,
                    'status' => $this->document->status,
                ]);
                return;
            }

            // Mark document as processing
            $this->document->markAsProcessing();

            // Extract metadata first
            $metadata = $parsingService->extractMetadata($this->document);
            if (!empty($metadata)) {
                $this->document->update([
                    'metadata' => array_merge($this->document->metadata ?? [], $metadata)
                ]);
            }

            // Extract content from document
            $extractedContent = $parsingService->extractContent($this->document);

            // Validate content quality
            $contentValidation = $parsingService->validateContentQuality($extractedContent);
            
            if (!$contentValidation['is_valid']) {
                $issues = implode(', ', $contentValidation['issues']);
                throw new \Exception("Content quality validation failed: {$issues}");
            }

            // Mark document as completed with extracted content
            $this->document->markAsCompleted($extractedContent);

            Log::info('Document content processing completed successfully', [
                'document_id' => $this->document->id,
                'content_length' => strlen($extractedContent),
                'word_count' => $contentValidation['statistics']['word_count'] ?? 0,
            ]);

        } catch (\Exception $e) {
            Log::error('Document content processing failed', [
                'document_id' => $this->document->id,
                'filename' => $this->document->original_filename,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Mark document as failed
            $this->document->markAsFailed($e->getMessage());

            // Re-throw exception to trigger retry logic
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Document processing job failed permanently', [
            'document_id' => $this->document->id,
            'filename' => $this->document->original_filename,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Ensure document is marked as failed
        if ($this->document->exists) {
            $this->document->markAsFailed('Processing failed after multiple attempts: ' . $exception->getMessage());
        }
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 120, 300]; // 30s, 2min, 5min
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(2); // Give up after 2 hours
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'document-processing',
            'document:' . $this->document->id,
            'user:' . $this->document->user_id,
            'type:' . $this->document->getFileExtension(),
        ];
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'document-processing:' . $this->document->id;
    }
}