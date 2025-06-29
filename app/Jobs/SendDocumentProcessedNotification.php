<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Send Document Processed Notification Job
 * 
 * Sends notification to user when document processing is completed successfully.
 */
class SendDocumentProcessedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('cat-simplifier.notifications.email.enabled')) {
            return;
        }

        try {
            $user = $this->document->user;
            
            if (!$user) {
                Log::warning('Cannot send notification: user not found', [
                    'document_id' => $this->document->id,
                ]);
                return;
            }

            // Check if user wants email notifications (you could add this to user preferences)
            if (!$this->userWantsNotification($user)) {
                return;
            }

            // Send the notification email
            Mail::to($user->email)->send(new \App\Mail\DocumentProcessedMail($this->document));

            Log::info('Document processed notification sent', [
                'document_id' => $this->document->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send document processed notification', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if user wants to receive notifications.
     */
    protected function userWantsNotification(User $user): bool
    {
        // In a real implementation, this would check user preferences
        // For now, assume all users want notifications
        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Document processed notification job failed', [
            'document_id' => $this->document->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

/**
 * Send Document Failed Notification Job
 * 
 * Sends notification to user when document processing fails.
 */
class SendDocumentFailedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Document $document
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('cat-simplifier.notifications.email.enabled')) {
            return;
        }

        try {
            $user = $this->document->user;
            
            if (!$user) {
                Log::warning('Cannot send failure notification: user not found', [
                    'document_id' => $this->document->id,
                ]);
                return;
            }

            // Check if user wants email notifications
            if (!$this->userWantsNotification($user)) {
                return;
            }

            // Send the failure notification email
            Mail::to($user->email)->send(new \App\Mail\DocumentFailedMail($this->document));

            Log::info('Document failed notification sent', [
                'document_id' => $this->document->id,
                'user_id' => $user->id,
                'user_email' => $user->email,
                'error' => $this->document->processing_error,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send document failed notification', [
                'document_id' => $this->document->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if user wants to receive notifications.
     */
    protected function userWantsNotification(User $user): bool
    {
        // In a real implementation, this would check user preferences
        return true;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Document failed notification job failed', [
            'document_id' => $this->document->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

/**
 * Send Weekly Summary Notification Job
 * 
 * Sends weekly summary of user activity and processing statistics.
 */
class SendWeeklySummaryNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;
    public $tries = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public array $summaryData
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('cat-simplifier.notifications.email.enabled')) {
            return;
        }

        if (!config('cat-simplifier.features.email_notifications')) {
            return;
        }

        try {
            // Check if user wants weekly summaries
            if (!$this->userWantsWeeklySummary($this->user)) {
                return;
            }

            // Only send if user has activity in the past week
            if ($this->summaryData['total_activity'] === 0) {
                Log::debug('Skipping weekly summary: no activity', [
                    'user_id' => $this->user->id,
                ]);
                return;
            }

            // Send the weekly summary email
            Mail::to($this->user->email)->send(
                new \App\Mail\WeeklySummaryMail($this->user, $this->summaryData)
            );

            Log::info('Weekly summary notification sent', [
                'user_id' => $this->user->id,
                'user_email' => $this->user->email,
                'activity_count' => $this->summaryData['total_activity'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send weekly summary notification', [
                'user_id' => $this->user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Check if user wants weekly summary emails.
     */
    protected function userWantsWeeklySummary(User $user): bool
    {
        // In a real implementation, this would check user email preferences
        // For now, assume users with recent activity want summaries
        return $user->documents()->where('created_at', '>=', now()->subWeek())->exists();
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Weekly summary notification job failed', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);
    }
}

/**
 * Send System Alert Notification Job
 * 
 * Sends system alerts to administrators for critical issues.
 */
class SendSystemAlertNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $alertType,
        public string $message,
        public array $context = []
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $adminEmails = $this->getAdminEmails();
            
            if (empty($adminEmails)) {
                Log::warning('No admin emails configured for system alerts');
                return;
            }

            foreach ($adminEmails as $email) {
                Mail::to($email)->send(
                    new \App\Mail\SystemAlertMail($this->alertType, $this->message, $this->context)
                );
            }

            Log::info('System alert notification sent', [
                'alert_type' => $this->alertType,
                'message' => $this->message,
                'recipients' => count($adminEmails),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send system alert notification', [
                'alert_type' => $this->alertType,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get admin email addresses.
     */
    protected function getAdminEmails(): array
    {
        // In a real implementation, this would come from config or database
        $emails = [
            config('cat-simplifier.notifications.email.from_address'),
            'admin@catdocs.com',
        ];

        return array_filter($emails);
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('System alert notification job failed permanently', [
            'alert_type' => $this->alertType,
            'message' => $this->message,
            'error' => $exception->getMessage(),
        ]);
    }
}

/**
 * Send Batch Completion Notification Job
 * 
 * Sends notification when a batch of jobs completes.
 */
class SendBatchCompletionNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60;
    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public User $user,
        public string $batchId,
        public array $batchResults
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!config('cat-simplifier.features.batch_processing')) {
            return;
        }

        if (!config('cat-simplifier.notifications.email.enabled')) {
            return;
        }

        try {
            // Send batch completion notification
            Mail::to($this->user->email)->send(
                new \App\Mail\BatchCompletionMail($this->user, $this->batchId, $this->batchResults)
            );

            Log::info('Batch completion notification sent', [
                'user_id' => $this->user->id,
                'batch_id' => $this->batchId,
                'total_jobs' => $this->batchResults['total'],
                'successful_jobs' => $this->batchResults['successful'],
                'failed_jobs' => $this->batchResults['failed'],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send batch completion notification', [
                'user_id' => $this->user->id,
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Batch completion notification job failed', [
            'user_id' => $this->user->id,
            'batch_id' => $this->batchId,
            'error' => $exception->getMessage(),
        ]);
    }
}