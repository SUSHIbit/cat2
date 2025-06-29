<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Queue Management & Monitoring
        $schedule->command('queue:monitor --alert')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/queue-monitor.log'));

        // Restart queue workers daily to prevent memory issues
        $schedule->command('queue:restart')
            ->daily()
            ->at('03:00')
            ->withoutOverlapping();

        // Clean up failed jobs older than 7 days
        $schedule->command('queue:prune-failed', ['--hours=168'])
            ->daily()
            ->at('02:00');

        // Document Processing & Cleanup
        $schedule->command('cat-simplifier:cleanup-documents')
            ->daily()
            ->at('01:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/cleanup.log'));

        // Clean up orphaned files (files not referenced in database)
        $schedule->command('cat-simplifier:cleanup-orphaned-files')
            ->weekly()
            ->sundays()
            ->at('02:00')
            ->withoutOverlapping();

        // Archive old completed documents
        $schedule->command('cat-simplifier:archive-documents')
            ->daily()
            ->at('04:00')
            ->withoutOverlapping();

        // User & Analytics Management
        $schedule->command('cat-simplifier:update-user-stats')
            ->hourly()
            ->withoutOverlapping();

        // Process analytics data (batch processing)
        $schedule->command('cat-simplifier:process-analytics')
            ->hourly()
            ->at(15) // 15 minutes past each hour
            ->withoutOverlapping();

        // Generate daily reports
        $schedule->command('cat-simplifier:generate-daily-report')
            ->daily()
            ->at('06:00')
            ->withoutOverlapping()
            ->emailOutputOnFailure('admin@catdocs.com');

        // Generate weekly summary reports
        $schedule->command('cat-simplifier:generate-weekly-report')
            ->weekly()
            ->mondays()
            ->at('07:00')
            ->withoutOverlapping()
            ->emailOutputOnFailure('admin@catdocs.com');

        // System Health & Maintenance
        $schedule->command('cat-simplifier:health-check')
            ->everyTenMinutes()
            ->withoutOverlapping()
            ->when(function () {
                return config('cat-simplifier.debug.log_processing_details');
            });

        // Backup important data
        $schedule->command('cat-simplifier:backup-data')
            ->daily()
            ->at('00:30')
            ->withoutOverlapping()
            ->when(function () {
                return config('cat-simplifier.backup.enabled');
            });

        // Optimize database tables
        $schedule->command('cat-simplifier:optimize-database')
            ->weekly()
            ->saturdays()
            ->at('23:00')
            ->withoutOverlapping();

        // Cache Management
        $schedule->command('cache:prune-stale-tags')
            ->hourly()
            ->at(30); // 30 minutes past each hour

        // Clear old cache entries
        $schedule->command('cat-simplifier:clear-old-cache')
            ->daily()
            ->at('05:00');

        // Notification Management
        $schedule->command('cat-simplifier:send-pending-notifications')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->when(function () {
                return config('cat-simplifier.notifications.email.enabled');
            });

        // Send weekly summary emails to users
        $schedule->command('cat-simplifier:send-weekly-summaries')
            ->weekly()
            ->mondays()
            ->at('08:00')
            ->withoutOverlapping()
            ->when(function () {
                return config('cat-simplifier.features.email_notifications');
            });

        // API & Rate Limiting
        $schedule->command('cat-simplifier:reset-rate-limits')
            ->hourly()
            ->at(0);

        // Clean up expired API tokens
        $schedule->command('sanctum:prune-expired --hours=24')
            ->daily()
            ->at('03:30')
            ->when(function () {
                return config('cat-simplifier.features.api_access');
            });

        // Development & Testing Tasks (only in non-production)
        if (!app()->environment('production')) {
            // Generate test data for development
            $schedule->command('cat-simplifier:generate-test-data')
                ->daily()
                ->at('22:00')
                ->when(function () {
                    return config('app.debug') && config('cat-simplifier.debug.mock_ai_responses');
                });

            // Log system metrics for development monitoring
            $schedule->command('cat-simplifier:log-metrics')
                ->everyFifteenMinutes()
                ->when(function () {
                    return config('cat-simplifier.debug.log_processing_details');
                });
        }

        // Emergency & Recovery Tasks
        $schedule->command('cat-simplifier:recover-stuck-jobs')
            ->everyThirtyMinutes()
            ->withoutOverlapping();

        // Monitor disk space and clean up if needed
        $schedule->command('cat-simplifier:monitor-disk-space')
            ->hourly()
            ->withoutOverlapping();

        // OpenAI Usage Tracking & Cost Management
        $schedule->command('cat-simplifier:track-ai-usage')
            ->hourly()
            ->at(5)
            ->withoutOverlapping();

        // Reset daily AI usage limits
        $schedule->command('cat-simplifier:reset-daily-limits')
            ->daily()
            ->at('00:00')
            ->withoutOverlapping();

        // Content Quality & Monitoring
        $schedule->command('cat-simplifier:analyze-content-quality')
            ->daily()
            ->at('07:30')
            ->withoutOverlapping();

        // Check for content policy violations
        $schedule->command('cat-simplifier:scan-content-violations')
            ->daily()
            ->at('08:30')
            ->withoutOverlapping()
            ->when(function () {
                return config('cat-simplifier.security.content_filtering.enabled');
            });

        // Security & Compliance
        $schedule->command('cat-simplifier:security-scan')
            ->daily()
            ->at('09:00')
            ->withoutOverlapping();

        // Rotate logs
        $schedule->command('cat-simplifier:rotate-logs')
            ->daily()
            ->at('23:30');

        // Performance Optimization
        $schedule->command('model:prune')
            ->daily()
            ->at('04:30');

        // Clear temporary files
        $schedule->command('cat-simplifier:clear-temp-files')
            ->daily()
            ->at('05:30');

        // Update application cache
        $schedule->command('cat-simplifier:warm-cache')
            ->daily()
            ->at('06:30');

        // Conditional tasks based on feature flags
        if (config('cat-simplifier.features.advanced_analytics')) {
            $schedule->command('cat-simplifier:advanced-analytics')
                ->daily()
                ->at('10:00');
        }

        if (config('cat-simplifier.features.batch_processing')) {
            $schedule->command('cat-simplifier:process-batch-jobs')
                ->everyFifteenMinutes()
                ->withoutOverlapping();
        }

        // Custom maintenance window (avoid peak hours)
        $schedule->command('cat-simplifier:maintenance-tasks')
            ->daily()
            ->between('01:00', '05:00')
            ->when(function () {
                // Only run during low-traffic hours
                return now()->hour >= 1 && now()->hour <= 5;
            });
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Get the timezone that should be used by default for scheduled events.
     */
    protected function scheduleTimezone(): ?string
    {
        return config('app.timezone');
    }
}