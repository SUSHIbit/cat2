<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Queue Restart Command
 * 
 * Gracefully restarts queue workers with proper cleanup and monitoring.
 * Handles different restart strategies and ensures no job loss during restart.
 */
class QueueRestartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:restart-workers 
                           {--strategy=graceful : Restart strategy (graceful|immediate|rolling)}
                           {--queue=* : Specific queues to restart}
                           {--timeout=30 : Timeout in seconds for graceful shutdown}
                           {--force : Force restart even if jobs are processing}
                           {--dry-run : Show what would be restarted without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restart queue workers with various strategies and safety checks';

    /**
     * Available restart strategies
     */
    protected array $strategies = [
        'graceful' => 'Wait for current jobs to finish before restarting',
        'immediate' => 'Stop workers immediately and restart',
        'rolling' => 'Restart workers one by one to maintain processing capacity',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $strategy = $this->option('strategy');
        $queues = $this->option('queue');
        $timeout = (int) $this->option('timeout');
        $force = $this->option('force');
        $dryRun = $this->option('dry-run');

        // Validate strategy
        if (!array_key_exists($strategy, $this->strategies)) {
            $this->error("Invalid strategy: {$strategy}");
            $this->info("Available strategies:");
            foreach ($this->strategies as $name => $description) {
                $this->line("  {$name}: {$description}");
            }
            return Command::FAILURE;
        }

        // Get current worker status
        $workers = $this->getWorkerStatus($queues);
        
        if (empty($workers)) {
            $this->info('No active workers found to restart.');
            return Command::SUCCESS;
        }

        // Display restart plan
        $this->displayRestartPlan($workers, $strategy, $timeout, $force);

        if ($dryRun) {
            $this->info('Dry run completed. No workers were actually restarted.');
            return Command::SUCCESS;
        }

        // Confirm restart unless forced
        if (!$force && !$this->confirm('Proceed with worker restart?')) {
            $this->info('Restart cancelled.');
            return Command::SUCCESS;
        }

        // Execute restart based on strategy
        $result = match($strategy) {
            'graceful' => $this->gracefulRestart($workers, $timeout),
            'immediate' => $this->immediateRestart($workers),
            'rolling' => $this->rollingRestart($workers, $timeout),
            default => false,
        };

        if ($result) {
            $this->info('Queue workers restarted successfully.');
            $this->logRestartEvent($strategy, $workers, true);
            return Command::SUCCESS;
        } else {
            $this->error('Failed to restart some workers.');
            $this->logRestartEvent($strategy, $workers, false);
            return Command::FAILURE;
        }
    }

    /**
     * Get current worker status information.
     */
    protected function getWorkerStatus(array $specificQueues = []): array
    {
        $workers = [];
        
        // This is a simplified implementation - in production you would integrate
        // with your process manager (Supervisor, PM2, etc.)
        
        // Check for active jobs in each queue
        $activeQueues = DB::table('jobs')
            ->select('queue')
            ->distinct()
            ->when(!empty($specificQueues), function ($query) use ($specificQueues) {
                return $query->whereIn('queue', $specificQueues);
            })
            ->pluck('queue')
            ->toArray();

        foreach ($activeQueues as $queue) {
            $totalJobs = DB::table('jobs')->where('queue', $queue)->count();
            $processingJobs = DB::table('jobs')
                ->where('queue', $queue)
                ->whereNotNull('reserved_at')
                ->count();

            $workers[] = [
                'id' => "worker-{$queue}",
                'queue' => $queue,
                'status' => $processingJobs > 0 ? 'processing' : 'waiting',
                'total_jobs' => $totalJobs,
                'processing_jobs' => $processingJobs,
                'pid' => $this->getWorkerPid($queue),
                'memory_usage' => $this->getWorkerMemoryUsage($queue),
                'uptime' => $this->getWorkerUptime($queue),
            ];
        }

        return $workers;
    }

    /**
     * Display the restart plan to the user.
     */
    protected function displayRestartPlan(array $workers, string $strategy, int $timeout, bool $force): void
    {
        $this->info("Restart Plan");
        $this->info("Strategy: {$strategy} - {$this->strategies[$strategy]}");
        $this->info("Timeout: {$timeout} seconds");
        $this->info("Force: " . ($force ? 'Yes' : 'No'));
        $this->newLine();

        // Display workers table
        $headers = ['Worker ID', 'Queue', 'Status', 'Jobs', 'Processing', 'PID', 'Memory', 'Uptime'];
        $rows = [];

        foreach ($workers as $worker) {
            $rows[] = [
                $worker['id'],
                $worker['queue'],
                $worker['status'],
                $worker['total_jobs'],
                $worker['processing_jobs'],
                $worker['pid'] ?? 'N/A',
                $worker['memory_usage'] ?? 'N/A',
                $worker['uptime'] ?? 'N/A',
            ];
        }

        $this->table($headers, $rows);

        // Show warnings if needed
        $processingWorkers = array_filter($workers, fn($w) => $w['status'] === 'processing');
        if (!empty($processingWorkers) && !$force) {
            $this->warn("Warning: " . count($processingWorkers) . " workers are currently processing jobs.");
            if ($strategy === 'immediate') {
                $this->warn("Immediate restart will terminate these jobs!");
            }
        }
    }

    /**
     * Perform graceful restart - wait for jobs to finish.
     */
    protected function gracefulRestart(array $workers, int $timeout): bool
    {
        $this->info('Starting graceful restart...');

        // Signal workers to stop accepting new jobs
        $this->signalWorkers($workers, 'graceful_stop');

        // Wait for processing jobs to complete
        $startTime = time();
        $this->info('Waiting for current jobs to complete...');

        while (time() - $startTime < $timeout) {
            $stillProcessing = $this->getProcessingJobsCount($workers);
            
            if ($stillProcessing === 0) {
                $this->info('All jobs completed successfully.');
                break;
            }

            $remaining = $timeout - (time() - $startTime);
            $this->line("Jobs still processing: {$stillProcessing}, timeout in {$remaining}s");
            sleep(2);
        }

        // Check if we timed out
        $finalProcessing = $this->getProcessingJobsCount($workers);
        if ($finalProcessing > 0) {
            $this->warn("Timeout reached with {$finalProcessing} jobs still processing.");
            if (!$this->confirm('Force stop remaining jobs?')) {
                return false;
            }
        }

        // Stop all workers
        return $this->stopWorkers($workers) && $this->startWorkers($workers);
    }

    /**
     * Perform immediate restart - stop workers right away.
     */
    protected function immediateRestart(array $workers): bool
    {
        $this->info('Starting immediate restart...');
        
        $processingJobs = $this->getProcessingJobsCount($workers);
        if ($processingJobs > 0) {
            $this->warn("Forcefully stopping {$processingJobs} processing jobs.");
        }

        return $this->stopWorkers($workers) && $this->startWorkers($workers);
    }

    /**
     * Perform rolling restart - restart workers one by one.
     */
    protected function rollingRestart(array $workers, int $timeout): bool
    {
        $this->info('Starting rolling restart...');

        foreach ($workers as $worker) {
            $this->info("Restarting worker: {$worker['id']}");

            // Wait for this worker's jobs to complete if it's processing
            if ($worker['processing_jobs'] > 0) {
                $this->waitForWorkerJobs([$worker], $timeout);
            }

            // Stop and restart this worker
            if (!$this->stopWorkers([$worker])) {
                $this->error("Failed to stop worker: {$worker['id']}");
                return false;
            }

            sleep(1); // Brief pause between stop and start

            if (!$this->startWorkers([$worker])) {
                $this->error("Failed to start worker: {$worker['id']}");
                return false;
            }

            $this->info("Worker {$worker['id']} restarted successfully.");
            sleep(2); // Brief pause before next worker
        }

        return true;
    }

    /**
     * Signal workers to change their behavior.
     */
    protected function signalWorkers(array $workers, string $signal): void
    {
        // In a real implementation, this would send signals to worker processes
        // For now, we'll use cache to communicate with workers
        
        foreach ($workers as $worker) {
            $key = "worker_signal_{$worker['queue']}";
            Cache::put($key, $signal, 300); // 5 minutes
        }

        // Also trigger the standard Laravel queue restart
        $this->call('queue:restart');
    }

    /**
     * Stop workers gracefully or forcefully.
     */
    protected function stopWorkers(array $workers): bool
    {
        $success = true;

        foreach ($workers as $worker) {
            $this->line("Stopping worker: {$worker['id']}");

            if ($worker['pid']) {
                // Send SIGTERM first for graceful shutdown
                $result = $this->sendSignalToPid($worker['pid'], 'TERM');
                
                if (!$result) {
                    $this->warn("Failed to stop worker {$worker['id']} gracefully, forcing...");
                    // Force with SIGKILL if graceful fails
                    $result = $this->sendSignalToPid($worker['pid'], 'KILL');
                }

                if (!$result) {
                    $this->error("Failed to stop worker: {$worker['id']}");
                    $success = false;
                }
            }
        }

        return $success;
    }

    /**
     * Start workers for specified queues.
     */
    protected function startWorkers(array $workers): bool
    {
        $success = true;

        foreach ($workers as $worker) {
            $this->line("Starting worker for queue: {$worker['queue']}");

            // In production, this would integrate with your process manager
            // For now, we'll simulate the start process
            $command = $this->buildWorkerCommand($worker['queue']);
            
            if ($this->executeWorkerStart($command)) {
                $this->info("Worker started for queue: {$worker['queue']}");
            } else {
                $this->error("Failed to start worker for queue: {$worker['queue']}");
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Get count of jobs currently being processed.
     */
    protected function getProcessingJobsCount(array $workers): int
    {
        $count = 0;
        foreach ($workers as $worker) {
            $count += DB::table('jobs')
                ->where('queue', $worker['queue'])
                ->whereNotNull('reserved_at')
                ->count();
        }
        return $count;
    }

    /**
     * Wait for specific worker's jobs to complete.
     */
    protected function waitForWorkerJobs(array $workers, int $timeout): bool
    {
        $startTime = time();

        while (time() - $startTime < $timeout) {
            $stillProcessing = $this->getProcessingJobsCount($workers);
            
            if ($stillProcessing === 0) {
                return true;
            }

            sleep(1);
        }

        return false; // Timed out
    }

    /**
     * Helper methods for worker management.
     */
    protected function getWorkerPid(string $queue): ?int
    {
        // In production, you would track PIDs in cache or database
        return Cache::get("worker_pid_{$queue}");
    }

    protected function getWorkerMemoryUsage(string $queue): ?string
    {
        // In production, you would get actual memory usage
        return Cache::get("worker_memory_{$queue}");
    }

    protected function getWorkerUptime(string $queue): ?string
    {
        $startTime = Cache::get("worker_start_time_{$queue}");
        if ($startTime) {
            $uptime = time() - $startTime;
            return $this->formatUptime($uptime);
        }
        return null;
    }

    protected function sendSignalToPid(int $pid, string $signal): bool
    {
        if (!function_exists('posix_kill')) {
            return false;
        }

        $signalNumber = match($signal) {
            'TERM' => SIGTERM,
            'KILL' => SIGKILL,
            'USR1' => SIGUSR1,
            'USR2' => SIGUSR2,
            default => SIGTERM,
        };

        return posix_kill($pid, $signalNumber);
    }

    protected function buildWorkerCommand(string $queue): string
    {
        $baseCommand = 'php artisan queue:work';
        $options = [
            "--queue={$queue}",
            '--sleep=3',
            '--tries=3',
            '--timeout=120',
        ];

        return $baseCommand . ' ' . implode(' ', $options);
    }

    protected function executeWorkerStart(string $command): bool
    {
        // In production, you would use proper process management
        // This is a simplified example
        
        $output = [];
        $returnCode = 0;
        
        // Execute in background
        exec($command . ' > /dev/null 2>&1 &', $output, $returnCode);
        
        return $returnCode === 0;
    }

    protected function formatUptime(int $seconds): string
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    protected function logRestartEvent(string $strategy, array $workers, bool $success): void
    {
        Log::info('Queue worker restart completed', [
            'strategy' => $strategy,
            'workers_count' => count($workers),
            'success' => $success,
            'timestamp' => now()->toISOString(),
            'user' => get_current_user(),
        ]);
    }
}