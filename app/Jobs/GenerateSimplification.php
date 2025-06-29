<?php

namespace App\Jobs;

use App\Models\Simplification;
use App\Services\OpenAIService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Generate Simplification Job
 * 
 * Background job that uses OpenAI API to generate cat story simplifications
 * from processed document content.
 */
class GenerateSimplification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 180; // 3 minutes
    public $tries = 3;
    public $maxExceptions = 2;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Simplification $simplification
    ) {
        // Set queue based on AI model (GPT-4 to priority queue)
        $queue = $simplification->ai_model === Simplification::MODEL_GPT_4 ? 'ai-priority' : 'ai-default';
        $this->onQueue($queue);
    }

    /**
     * Execute the job.
     */
    public function handle(OpenAIService $openAIService): void
    {
        Log::info('Starting simplification generation', [
            'simplification_id' => $this->simplification->id,
            'document_id' => $this->simplification->document_id,
            'ai_model' => $this->simplification->ai_model,
            'complexity_level' => $this->simplification->complexity_level,
        ]);

        try {
            // Check if simplification is still pending
            if (!$this->simplification->isPending()) {
                Log::warning('Simplification is not in pending state', [
                    'simplification_id' => $this->simplification->id,
                    'status' => $this->simplification->status,
                ]);
                return;
            }

            // Verify document is processed and has content
            $document = $this->simplification->document;
            if (!$document->isProcessed() || empty($document->extracted_content)) {
                throw new \Exception('Document is not ready for simplification');
            }

            // Mark simplification as processing
            $this->simplification->markAsProcessing();

            // Generate simplification using OpenAI
            $result = $openAIService->generateSimplification($this->simplification);

            // Mark simplification as completed with results
            $this->simplification->markAsCompleted($result);

            Log::info('Simplification generation completed successfully', [
                'simplification_id' => $this->simplification->id,
                'tokens_used' => $result['tokens_used'] ?? 0,
                'processing_cost' => $result['processing_cost'] ?? 0,
                'processing_time' => $result['processing_time_seconds'] ?? 0,
                'readability_score' => $result['readability_score'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Simplification generation failed', [
                'simplification_id' => $this->simplification->id,
                'document_id' => $this->simplification->document_id,
                'ai_model' => $this->simplification->ai_model,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Mark simplification as failed
            $this->simplification->markAsFailed($e->getMessage());

            // Re-throw exception to trigger retry logic for certain errors
            if ($this->shouldRetry($e)) {
                throw $e;
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Simplification generation job failed permanently', [
            'simplification_id' => $this->simplification->id,
            'document_id' => $this->simplification->document_id,
            'ai_model' => $this->simplification->ai_model,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);

        // Ensure simplification is marked as failed
        if ($this->simplification->exists) {
            $errorMessage = $this->getFailureMessage($exception);
            $this->simplification->markAsFailed($errorMessage);
        }
    }

    /**
     * Determine if the job should be retried based on the exception.
     */
    protected function shouldRetry(\Exception $exception): bool
    {
        $retryableErrors = [
            'rate limit',
            'timeout',
            'server error',
            'internal error',
            'service unavailable',
            'temporarily unavailable',
        ];

        $errorMessage = strtolower($exception->getMessage());
        
        foreach ($retryableErrors as $retryableError) {
            if (strpos($errorMessage, $retryableError) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get user-friendly failure message.
     */
    protected function getFailureMessage(\Throwable $exception): string
    {
        $errorMessage = $exception->getMessage();
        
        // Map technical errors to user-friendly messages
        if (strpos($errorMessage, 'rate limit') !== false) {
            return 'AI service is currently busy. Please try again later.';
        }
        
        if (strpos($errorMessage, 'timeout') !== false) {
            return 'Processing took too long. Please try with a shorter document.';
        }
        
        if (strpos($errorMessage, 'content policy') !== false || strpos($errorMessage, 'safety') !== false) {
            return 'Document content could not be processed due to content guidelines.';
        }
        
        if (strpos($errorMessage, 'token') !== false) {
            return 'Document is too long for processing. Please try with a shorter document.';
        }
        
        if (strpos($errorMessage, 'API key') !== false || strpos($errorMessage, 'authentication') !== false) {
            return 'AI service authentication failed. Please contact support.';
        }
        
        // Generic message for other errors
        return 'AI processing failed. Please try again or contact support if the problem persists.';
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        // Progressive backoff for AI service rate limits
        return [60, 300, 900]; // 1min, 5min, 15min
    }

    /**
     * Determine when to stop retrying the job.
     */
    public function retryUntil(): \DateTime
    {
        return now()->addHours(6); // Give up after 6 hours
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'simplification-generation',
            'simplification:' . $this->simplification->id,
            'document:' . $this->simplification->document_id,
            'user:' . $this->simplification->user_id,
            'model:' . $this->simplification->ai_model,
            'complexity:' . $this->simplification->complexity_level,
        ];
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'simplification-generation:' . $this->simplification->id;
    }

    /**
     * Handle job middleware.
     */
    public function middleware(): array
    {
        return [
            new \Illuminate\Queue\Middleware\RateLimited('ai-requests'),
        ];
    }
}