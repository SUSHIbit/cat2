<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Cleanup Document Files Job
 * 
 * Background job for cleaning up document files and related artifacts
 * after document deletion or archival periods expire.
 */
class CleanupDocumentFiles implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120; // 2 minutes
    public $tries = 3;
    public $maxExceptions = 1;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $filePath,
        public bool $forceDelete = false,
        public array $additionalPaths = []
    ) {
        $this->onQueue('default');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting file cleanup', [
            'file_path' => $this->filePath,
            'force_delete' => $this->forceDelete,
            'additional_paths' => $this->additionalPaths,
        ]);

        try {
            // Clean up main file
            $this->cleanupFile($this->filePath);

            // Clean up additional files if specified
            foreach ($this->additionalPaths as $path) {
                $this->cleanupFile($path);
            }

            Log::info('File cleanup completed successfully', [
                'file_path' => $this->filePath,
                'additional_files_cleaned' => count($this->additionalPaths),
            ]);

        } catch (\Exception $e) {
            Log::error('File cleanup failed', [
                'file_path' => $this->filePath,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Clean up a specific file.
     */
    protected function cleanupFile(string $filePath): void
    {
        // Skip if file doesn't exist
        if (!Storage::exists($filePath)) {
            Log::debug('File does not exist, skipping cleanup', ['file_path' => $filePath]);
            return;
        }

        // Perform safety checks before deletion
        if (!$this->forceDelete && !$this->isSafeToDelete($filePath)) {
            Log::warning('File not safe to delete, skipping', ['file_path' => $filePath]);
            return;
        }

        // Delete the file
        if (Storage::delete($filePath)) {
            Log::info('File deleted successfully', ['file_path' => $filePath]);
        } else {
            throw new \Exception("Failed to delete file: {$filePath}");
        }

        // Clean up empty directories
        $this->cleanupEmptyDirectories($filePath);
    }

    /**
     * Check if file is safe to delete.
     */
    protected function isSafeToDelete(string $filePath): bool
    {
        // Check if file is in the correct directory
        $allowedPaths = [
            'documents/',
            'temp/',
            'uploads/',
        ];

        $isInAllowedPath = false;
        foreach ($allowedPaths as $allowedPath) {
            if (str_starts_with($filePath, $allowedPath)) {
                $isInAllowedPath = true;
                break;
            }
        }

        if (!$isInAllowedPath) {
            Log::warning('File not in allowed path for deletion', ['file_path' => $filePath]);
            return false;
        }

        // Check if file is still referenced in database
        if ($this->isFileStillReferenced($filePath)) {
            Log::warning('File still referenced in database', ['file_path' => $filePath]);
            return false;
        }

        return true;
    }

    /**
     * Check if file is still referenced in the database.
     */
    protected function isFileStillReferenced(string $filePath): bool
    {
        // Check documents table
        $documentExists = \App\Models\Document::withTrashed()
            ->where('file_path', $filePath)
            ->exists();

        if ($documentExists) {
            return true;
        }

        // Add other checks if needed (e.g., backup tables, etc.)
        return false;
    }

    /**
     * Clean up empty directories.
     */
    protected function cleanupEmptyDirectories(string $filePath): void
    {
        $directory = dirname($filePath);
        
        // Don't clean up root directories
        $protectedDirs = ['documents', 'temp', 'uploads', ''];
        if (in_array($directory, $protectedDirs)) {
            return;
        }

        try {
            // Check if directory is empty
            $files = Storage::files($directory);
            $subdirs = Storage::directories($directory);

            if (empty($files) && empty($subdirs)) {
                Storage::deleteDirectory($directory);
                Log::debug('Empty directory cleaned up', ['directory' => $directory]);

                // Recursively clean parent directories
                $this->cleanupEmptyDirectories($directory);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to cleanup empty directory', [
                'directory' => $directory,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('File cleanup job failed permanently', [
            'file_path' => $this->filePath,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [30, 120, 300]; // 30s, 2min, 5min
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'file-cleanup',
            'maintenance',
            'storage',
        ];
    }

    /**
     * The unique ID of the job.
     */
    public function uniqueId(): string
    {
        return 'file-cleanup:' . md5($this->filePath);
    }
}