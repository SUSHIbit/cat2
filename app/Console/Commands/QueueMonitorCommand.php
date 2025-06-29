<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * Queue Monitor Command
 * 
 * Monitors queue health, job processing statistics, and system performance.
 * Provides detailed reporting and alerting for queue management.
 */
class QueueMonitorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:monitor 
                           {--queue=* : Specific queues to monitor}
                           {--interval=30 : Refresh interval in seconds}
                           {--alert : Send alerts for issues}
                           {--export= : Export report to file}
                           {--json : Output in JSON format}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor queue health, performance, and statistics';

    /**
     * Queue names to monitor
     */
    protected array $defaultQueues = [
        'default',
        'ai-default', 
        'ai-priority',
        'heavy',
        'notifications'
    ];

    /**
     * Alert thresholds
     */
    protected array $alertThresholds = [
        'failed_jobs_count' => 10,
        'old_jobs_minutes' => 30,
        'queue_size_warning' => 100,
        'queue_size_critical' => 500,
        'processing_time_warning' => 300, // 5 minutes
        'processing_time_critical' => 900, // 15 minutes
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $queues = $this->option('queue') ?: $this->defaultQueues;
        $interval = (int) $this->option('interval');
        $shouldAlert = $this->option('alert');
        $exportFile = $this->option('export');
        $jsonOutput = $this->option('json');

        if ($interval > 0) {
            return $this->runContinuousMonitoring($queues, $interval, $shouldAlert, $jsonOutput);
        }

        $report = $this->generateQueueReport($queues, $shouldAlert);

        if ($jsonOutput) {
            $this->line(json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $this->displayReport($report);
        }

        if ($exportFile) {
            $this->exportReport($report, $exportFile, $jsonOutput);
        }

        return Command::SUCCESS;
    }

    /**
     * Run continuous monitoring with specified interval.
     */
    protected function runContinuousMonitoring(array $queues, int $interval, bool $shouldAlert, bool $jsonOutput): int
    {
        $this->info("Starting continuous queue monitoring (refresh every {$interval}s)");
        $this->info("Press Ctrl+C to stop monitoring");

        while (true) {
            if (!$jsonOutput) {
                // Clear screen for better display
                system('clear');
                $this->info("Queue Monitor - " . now()->format('Y-m-d H:i:s'));
                $this->info(str_repeat('=', 60));
            }

            $report = $this->generateQueueReport($queues, $shouldAlert);

            if ($jsonOutput) {
                $this->line(json_encode($report, JSON_PRETTY_PRINT));
            } else {
                $this->displayReport($report);
            }

            sleep($interval);
        }

        return Command::SUCCESS;
    }

    /**
     * Generate comprehensive queue report.
     */
    protected function generateQueueReport(array $queues, bool $shouldAlert): array
    {
        $report = [
            'timestamp' => now()->toISOString(),
            'system_status' => $this->getSystemStatus(),
            'queue_stats' => [],
            'failed_jobs' => $this->getFailedJobsStats(),
            'processing_stats' => $this->getProcessingStats(),
            'alerts' => [],
            'recommendations' => [],
        ];

        foreach ($queues as $queueName) {
            $queueStats = $this->getQueueStats($queueName);
            $report['queue_stats'][$queueName] = $queueStats;

            if ($shouldAlert) {
                $alerts = $this->checkQueueAlerts($queueName, $queueStats);
                if (!empty($alerts)) {
                    $report['alerts'][$queueName] = $alerts;
                }
            }
        }

        if ($shouldAlert) {
            $report['recommendations'] = $this->generateRecommendations($report);
        }

        return $report;
    }

    /**
     * Get overall system status.
     */
    protected function getSystemStatus(): array
    {
        return [
            'workers_running' => $this->getActiveWorkersCount(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_load' => $this->getCpuLoad(),
            'disk_usage' => $this->getDiskUsage(),
            'database_connections' => $this->getDatabaseConnections(),
        ];
    }

    /**
     * Get statistics for a specific queue.
     */
    protected function getQueueStats(string $queueName): array
    {
        $totalJobs = DB::table('jobs')->where('queue', $queueName)->count();
        $processingJobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->whereNotNull('reserved_at')
            ->count();
        
        $waitingJobs = $totalJobs - $processingJobs;
        
        $oldestJob = DB::table('jobs')
            ->where('queue', $queueName)
            ->whereNull('reserved_at')
            ->orderBy('created_at')
            ->first();

        $averageWaitTime = $this->getAverageWaitTime($queueName);
        $throughput = $this->getThroughput($queueName);

        return [
            'total_jobs' => $totalJobs,
            'waiting_jobs' => $waitingJobs,
            'processing_jobs' => $processingJobs,
            'oldest_job_age_minutes' => $oldestJob ? 
                now()->diffInMinutes($oldestJob->created_at) : 0,
            'average_wait_time_seconds' => $averageWaitTime,
            'throughput_per_hour' => $throughput,
            'status' => $this->determineQueueStatus($totalJobs, $waitingJobs, $oldestJob),
        ];
    }

    /**
     * Get failed jobs statistics.
     */
    protected function getFailedJobsStats(): array
    {
        $totalFailed = DB::table('failed_jobs')->count();
        $recentFailed = DB::table('failed_jobs')
            ->where('failed_at', '>=', now()->subHour())
            ->count();

        $failuresByQueue = DB::table('failed_jobs')
            ->selectRaw('JSON_EXTRACT(payload, "$.data.command") as job_class, count(*) as count')
            ->groupBy('job_class')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $commonErrors = DB::table('failed_jobs')
            ->selectRaw('LEFT(exception, 100) as error_preview, count(*) as count')
            ->groupBy('error_preview')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        return [
            'total_failed' => $totalFailed,
            'recent_failed' => $recentFailed,
            'failures_by_type' => $failuresByQueue->toArray(),
            'common_errors' => $commonErrors->toArray(),
        ];
    }

    /**
     * Get processing performance statistics.
     */
    protected function getProcessingStats(): array
    {
        // Get statistics from cache or calculate
        return Cache::remember('queue_processing_stats', 300, function () {
            return [
                'avg_document_processing_time' => $this->getAverageDocumentProcessingTime(),
                'avg_ai_processing_time' => $this->getAverageAiProcessingTime(),
                'success_rate_24h' => $this->getSuccessRate(24),
                'total_processed_today' => $this->getTotalProcessedToday(),
                'peak_processing_hour' => $this->getPeakProcessingHour(),
            ];
        });
    }

    /**
     * Check for queue-specific alerts.
     */
    protected function checkQueueAlerts(string $queueName, array $stats): array
    {
        $alerts = [];

        // Check queue size
        if ($stats['waiting_jobs'] >= $this->alertThresholds['queue_size_critical']) {
            $alerts[] = [
                'level' => 'critical',
                'message' => "Queue size critical: {$stats['waiting_jobs']} jobs waiting",
                'metric' => 'queue_size',
                'value' => $stats['waiting_jobs'],
            ];
        } elseif ($stats['waiting_jobs'] >= $this->alertThresholds['queue_size_warning']) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Queue size high: {$stats['waiting_jobs']} jobs waiting",
                'metric' => 'queue_size',
                'value' => $stats['waiting_jobs'],
            ];
        }

        // Check job age
        if ($stats['oldest_job_age_minutes'] >= $this->alertThresholds['old_jobs_minutes']) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Old jobs detected: oldest job is {$stats['oldest_job_age_minutes']} minutes old",
                'metric' => 'job_age',
                'value' => $stats['oldest_job_age_minutes'],
            ];
        }

        // Check processing time
        if ($stats['average_wait_time_seconds'] >= $this->alertThresholds['processing_time_critical']) {
            $alerts[] = [
                'level' => 'critical',
                'message' => "Processing time critical: {$stats['average_wait_time_seconds']}s average wait",
                'metric' => 'processing_time',
                'value' => $stats['average_wait_time_seconds'],
            ];
        } elseif ($stats['average_wait_time_seconds'] >= $this->alertThresholds['processing_time_warning']) {
            $alerts[] = [
                'level' => 'warning',
                'message' => "Processing time high: {$stats['average_wait_time_seconds']}s average wait",
                'metric' => 'processing_time',
                'value' => $stats['average_wait_time_seconds'],
            ];
        }

        return $alerts;
    }

    /**
     * Generate recommendations based on current status.
     */
    protected function generateRecommendations(array $report): array
    {
        $recommendations = [];

        // Check if we need more workers
        $totalWaiting = array_sum(array_column($report['queue_stats'], 'waiting_jobs'));
        if ($totalWaiting > 50) {
            $recommendations[] = [
                'type' => 'scaling',
                'priority' => 'high',
                'message' => 'Consider starting additional queue workers to handle backlog',
                'action' => 'php artisan queue:work --sleep=3 --tries=3',
            ];
        }

        // Check for failed job patterns
        if ($report['failed_jobs']['recent_failed'] > 5) {
            $recommendations[] = [
                'type' => 'reliability',
                'priority' => 'medium',
                'message' => 'High failure rate detected, investigate common error patterns',
                'action' => 'Review failed jobs and error logs',
            ];
        }

        // Performance recommendations
        foreach ($report['queue_stats'] as $queueName => $stats) {
            if ($stats['throughput_per_hour'] < 10 && $stats['waiting_jobs'] > 0) {
                $recommendations[] = [
                    'type' => 'performance',
                    'priority' => 'medium',
                    'message' => "Low throughput on {$queueName} queue",
                    'action' => "Consider optimizing job processing or adding dedicated workers",
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Display formatted report in console.
     */
    protected function displayReport(array $report): void
    {
        // System Status
        $this->info('System Status');
        $this->line('Workers Running: ' . $report['system_status']['workers_running']);
        $this->line('Memory Usage: ' . $report['system_status']['memory_usage']);
        $this->line('CPU Load: ' . $report['system_status']['cpu_load']);
        $this->newLine();

        // Queue Statistics Table
        $headers = ['Queue', 'Total', 'Waiting', 'Processing', 'Oldest (min)', 'Throughput/hr', 'Status'];
        $rows = [];

        foreach ($report['queue_stats'] as $queueName => $stats) {
            $rows[] = [
                $queueName,
                $stats['total_jobs'],
                $stats['waiting_jobs'],
                $stats['processing_jobs'],
                $stats['oldest_job_age_minutes'],
                $stats['throughput_per_hour'],
                $this->formatStatus($stats['status']),
            ];
        }

        $this->table($headers, $rows);

        // Failed Jobs Summary
        if ($report['failed_jobs']['total_failed'] > 0) {
            $this->warn('Failed Jobs');
            $this->line('Total: ' . $report['failed_jobs']['total_failed']);
            $this->line('Recent (1h): ' . $report['failed_jobs']['recent_failed']);
            $this->newLine();
        }

        // Alerts
        if (!empty($report['alerts'])) {
            $this->error('ALERTS');
            foreach ($report['alerts'] as $queueName => $alerts) {
                $this->line("<comment>Queue: {$queueName}</comment>");
                foreach ($alerts as $alert) {
                    $color = $alert['level'] === 'critical' ? 'error' : 'warn';
                    $this->$color("  [{$alert['level']}] {$alert['message']}");
                }
            }
            $this->newLine();
        }

        // Recommendations
        if (!empty($report['recommendations'])) {
            $this->info('Recommendations');
            foreach ($report['recommendations'] as $rec) {
                $this->line("<comment>[{$rec['priority']}]</comment> {$rec['message']}");
                $this->line("  Action: {$rec['action']}");
            }
        }
    }

    /**
     * Export report to file.
     */
    protected function exportReport(array $report, string $filename, bool $jsonFormat): void
    {
        $content = $jsonFormat ? 
            json_encode($report, JSON_PRETTY_PRINT) : 
            $this->formatReportAsText($report);

        file_put_contents($filename, $content);
        $this->info("Report exported to: {$filename}");
    }

    /**
     * Helper methods for statistics calculation
     */
    protected function getActiveWorkersCount(): int
    {
        // This is a simplified implementation - in production you might check process lists
        return (int) Cache::get('active_workers_count', 0);
    }

    protected function getMemoryUsage(): string
    {
        return round(memory_get_usage(true) / 1024 / 1024, 2) . ' MB';
    }

    protected function getCpuLoad(): string
    {
        $load = sys_getloadavg();
        return $load ? round($load[0], 2) : 'N/A';
    }

    protected function getDiskUsage(): string
    {
        $bytes = disk_free_space('.');
        return $bytes ? round($bytes / 1024 / 1024 / 1024, 2) . ' GB free' : 'N/A';
    }

    protected function getDatabaseConnections(): int
    {
        try {
            $result = DB::select('SHOW STATUS LIKE "Threads_connected"');
            return $result ? (int) $result[0]->Value : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getAverageWaitTime(string $queueName): float
    {
        $jobs = DB::table('jobs')
            ->where('queue', $queueName)
            ->whereNull('reserved_at')
            ->select('created_at')
            ->get();

        if ($jobs->isEmpty()) {
            return 0;
        }

        $totalWaitTime = 0;
        $now = now();

        foreach ($jobs as $job) {
            $totalWaitTime += $now->diffInSeconds($job->created_at);
        }

        return round($totalWaitTime / $jobs->count(), 2);
    }

    protected function getThroughput(string $queueName): int
    {
        // Get completed jobs from the last hour (approximation using failed_jobs and current queue state)
        $oneHourAgo = now()->subHour();
        
        // This is a simplified calculation - in production you might track this in a separate table
        $completedJobs = Cache::remember("throughput_{$queueName}", 300, function () use ($queueName, $oneHourAgo) {
            // Estimate based on documents and simplifications processed
            if ($queueName === 'default' || $queueName === 'heavy') {
                return DB::table('documents')
                    ->where('processed_at', '>=', $oneHourAgo)
                    ->count();
            }
            
            if (str_contains($queueName, 'ai')) {
                return DB::table('simplifications')
                    ->where('processed_at', '>=', $oneHourAgo)
                    ->count();
            }
            
            return 0;
        });

        return $completedJobs;
    }

    protected function determineQueueStatus(int $totalJobs, int $waitingJobs, $oldestJob): string
    {
        if ($totalJobs === 0) {
            return 'idle';
        }
        
        if ($waitingJobs >= $this->alertThresholds['queue_size_critical']) {
            return 'critical';
        }
        
        if ($waitingJobs >= $this->alertThresholds['queue_size_warning']) {
            return 'warning';
        }
        
        if ($oldestJob && now()->diffInMinutes($oldestJob->created_at) >= $this->alertThresholds['old_jobs_minutes']) {
            return 'warning';
        }
        
        if ($waitingJobs > 0) {
            return 'active';
        }
        
        return 'healthy';
    }

    protected function getAverageDocumentProcessingTime(): float
    {
        $result = DB::table('documents')
            ->whereNotNull('processed_at')
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('AVG(TIMESTAMPDIFF(SECOND, created_at, processed_at)) as avg_time')
            ->first();

        return $result ? round($result->avg_time, 2) : 0;
    }

    protected function getAverageAiProcessingTime(): float
    {
        $result = DB::table('simplifications')
            ->whereNotNull('processing_time_seconds')
            ->where('created_at', '>=', now()->subDays(7))
            ->avg('processing_time_seconds');

        return $result ? round($result, 2) : 0;
    }

    protected function getSuccessRate(int $hours): float
    {
        $since = now()->subHours($hours);
        
        $totalJobs = DB::table('simplifications')
            ->where('created_at', '>=', $since)
            ->count();
            
        $successfulJobs = DB::table('simplifications')
            ->where('created_at', '>=', $since)
            ->where('status', 'completed')
            ->count();

        if ($totalJobs === 0) {
            return 100;
        }

        return round(($successfulJobs / $totalJobs) * 100, 2);
    }

    protected function getTotalProcessedToday(): int
    {
        $today = now()->startOfDay();
        
        $documents = DB::table('documents')
            ->where('processed_at', '>=', $today)
            ->count();
            
        $simplifications = DB::table('simplifications')
            ->where('processed_at', '>=', $today)
            ->where('status', 'completed')
            ->count();

        return $documents + $simplifications;
    }

    protected function getPeakProcessingHour(): array
    {
        $result = DB::table('simplifications')
            ->where('processed_at', '>=', now()->subDays(7))
            ->where('status', 'completed')
            ->selectRaw('HOUR(processed_at) as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->orderByDesc('count')
            ->first();

        if (!$result) {
            return ['hour' => 'N/A', 'count' => 0];
        }

        return [
            'hour' => $result->hour . ':00',
            'count' => $result->count
        ];
    }

    protected function formatStatus(string $status): string
    {
        return match($status) {
            'idle' => '<info>Idle</info>',
            'healthy' => '<info>Healthy</info>',
            'active' => '<comment>Active</comment>',
            'warning' => '<comment>Warning</comment>',
            'critical' => '<error>Critical</error>',
            default => $status,
        };
    }

    protected function formatReportAsText(array $report): string
    {
        $text = "Queue Monitor Report\n";
        $text .= "Generated: " . $report['timestamp'] . "\n";
        $text .= str_repeat("=", 60) . "\n\n";

        // System Status
        $text .= "SYSTEM STATUS\n";
        $text .= "Workers Running: " . $report['system_status']['workers_running'] . "\n";
        $text .= "Memory Usage: " . $report['system_status']['memory_usage'] . "\n";
        $text .= "CPU Load: " . $report['system_status']['cpu_load'] . "\n";
        $text .= "Database Connections: " . $report['system_status']['database_connections'] . "\n\n";

        // Queue Statistics
        $text .= "QUEUE STATISTICS\n";
        foreach ($report['queue_stats'] as $queueName => $stats) {
            $text .= "Queue: {$queueName}\n";
            $text .= "  Total Jobs: {$stats['total_jobs']}\n";
            $text .= "  Waiting: {$stats['waiting_jobs']}\n";
            $text .= "  Processing: {$stats['processing_jobs']}\n";
            $text .= "  Oldest Job: {$stats['oldest_job_age_minutes']} minutes\n";
            $text .= "  Throughput: {$stats['throughput_per_hour']}/hour\n";
            $text .= "  Status: {$stats['status']}\n\n";
        }

        // Failed Jobs
        if ($report['failed_jobs']['total_failed'] > 0) {
            $text .= "FAILED JOBS\n";
            $text .= "Total Failed: " . $report['failed_jobs']['total_failed'] . "\n";
            $text .= "Recent Failed (1h): " . $report['failed_jobs']['recent_failed'] . "\n\n";
        }

        // Processing Stats
        $text .= "PROCESSING STATISTICS\n";
        $text .= "Avg Document Processing: " . $report['processing_stats']['avg_document_processing_time'] . "s\n";
        $text .= "Avg AI Processing: " . $report['processing_stats']['avg_ai_processing_time'] . "s\n";
        $text .= "Success Rate (24h): " . $report['processing_stats']['success_rate_24h'] . "%\n";
        $text .= "Total Processed Today: " . $report['processing_stats']['total_processed_today'] . "\n";
        $text .= "Peak Hour: " . $report['processing_stats']['peak_processing_hour']['hour'] . 
                 " ({$report['processing_stats']['peak_processing_hour']['count']} jobs)\n\n";

        // Alerts
        if (!empty($report['alerts'])) {
            $text .= "ALERTS\n";
            foreach ($report['alerts'] as $queueName => $alerts) {
                $text .= "Queue: {$queueName}\n";
                foreach ($alerts as $alert) {
                    $text .= "  [{$alert['level']}] {$alert['message']}\n";
                }
            }
            $text .= "\n";
        }

        // Recommendations
        if (!empty($report['recommendations'])) {
            $text .= "RECOMMENDATIONS\n";
            foreach ($report['recommendations'] as $rec) {
                $text .= "[{$rec['priority']}] {$rec['message']}\n";
                $text .= "Action: {$rec['action']}\n\n";
            }
        }

        return $text;
    }
}