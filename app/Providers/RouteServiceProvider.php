<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // Standard API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Cat Document Simplifier specific rate limiters
        $this->configureCatSimplifierRateLimits();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure rate limiters specific to Cat Document Simplifier.
     */
    protected function configureCatSimplifierRateLimits(): void
    {
        // AI request rate limiting (for queue jobs)
        RateLimiter::for('ai-requests', function (Request $request) {
            $userId = $request->user()?->id ?? 'guest';
            
            return [
                // 20 AI requests per minute per user
                Limit::perMinute(20)->by("ai-user-{$userId}"),
                
                // 1000 AI requests per hour per user
                Limit::perHour(1000)->by("ai-user-hour-{$userId}"),
                
                // Global AI request limit (to protect OpenAI quota)
                Limit::perMinute(100)->by('ai-global'),
                Limit::perHour(2000)->by('ai-global-hour'),
            ];
        });

        // Document upload rate limiting
        RateLimiter::for('document-upload', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // 10 uploads per minute per user
                Limit::perMinute(10)->by("upload-user-{$userId}"),
                
                // 50 uploads per hour per user
                Limit::perHour(50)->by("upload-user-hour-{$userId}"),
                
                // 200 uploads per day per user
                Limit::perDay(200)->by("upload-user-day-{$userId}"),
            ];
        });

        // Simplification creation rate limiting
        RateLimiter::for('simplification-create', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // 5 simplifications per minute per user
                Limit::perMinute(5)->by("simplify-user-{$userId}"),
                
                // 30 simplifications per hour per user
                Limit::perHour(30)->by("simplify-user-hour-{$userId}"),
                
                // 100 simplifications per day per user
                Limit::perDay(100)->by("simplify-user-day-{$userId}"),
            ];
        });

        // Public content access rate limiting
        RateLimiter::for('public-content', function (Request $request) {
            return [
                // 100 requests per minute per IP for public content
                Limit::perMinute(100)->by($request->ip()),
                
                // 1000 requests per hour per IP
                Limit::perHour(1000)->by("public-hour-{$request->ip()}"),
            ];
        });

        // Download rate limiting
        RateLimiter::for('downloads', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // 20 downloads per minute per user
                Limit::perMinute(20)->by("download-user-{$userId}"),
                
                // 100 downloads per hour per user
                Limit::perHour(100)->by("download-user-hour-{$userId}"),
            ];
        });

        // Authentication rate limiting
        RateLimiter::for('auth', function (Request $request) {
            return [
                // 5 login attempts per minute per IP
                Limit::perMinute(5)->by($request->ip()),
                
                // 20 login attempts per hour per IP
                Limit::perHour(20)->by("auth-hour-{$request->ip()}"),
            ];
        });

        // Search rate limiting
        RateLimiter::for('search', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // 30 searches per minute per user
                Limit::perMinute(30)->by("search-user-{$userId}"),
                
                // 500 searches per hour per user
                Limit::perHour(500)->by("search-user-hour-{$userId}"),
            ];
        });

        // Queue processing rate limiting (for internal use)
        RateLimiter::for('queue-processing', function () {
            return [
                // Global queue processing limits to prevent overwhelming the system
                Limit::perMinute(200)->by('queue-global'),
                Limit::perHour(5000)->by('queue-global-hour'),
            ];
        });

        // OpenAI API specific rate limiting
        RateLimiter::for('openai-api', function () {
            return [
                // Conservative limits to stay within OpenAI's rate limits
                Limit::perMinute(50)->by('openai-requests'),
                Limit::perHour(1000)->by('openai-requests-hour'),
                
                // Token-based limiting (approximate)
                Limit::perMinute(150000)->by('openai-tokens'), // ~150k tokens per minute
                Limit::perHour(1000000)->by('openai-tokens-hour'), // ~1M tokens per hour
            ];
        });

        // Admin operations rate limiting
        RateLimiter::for('admin-operations', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // More relaxed limits for admin operations
                Limit::perMinute(100)->by("admin-{$userId}"),
                Limit::perHour(2000)->by("admin-hour-{$userId}"),
            ];
        });

        // Bulk operations rate limiting
        RateLimiter::for('bulk-operations', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // Limited bulk operations to prevent abuse
                Limit::perMinute(2)->by("bulk-user-{$userId}"),
                Limit::perHour(10)->by("bulk-user-hour-{$userId}"),
            ];
        });

        // File processing rate limiting
        RateLimiter::for('file-processing', function () {
            return [
                // Global file processing limits
                Limit::perMinute(50)->by('file-processing-global'),
                Limit::perHour(1000)->by('file-processing-global-hour'),
            ];
        });

        // Notification rate limiting
        RateLimiter::for('notifications', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // Prevent notification spam
                Limit::perMinute(10)->by("notification-user-{$userId}"),
                Limit::perHour(50)->by("notification-user-hour-{$userId}"),
            ];
        });

        // Report generation rate limiting
        RateLimiter::for('reports', function (Request $request) {
            $userId = $request->user()?->id ?? $request->ip();
            
            return [
                // Limited report generation to prevent resource exhaustion
                Limit::perMinute(2)->by("report-user-{$userId}"),
                Limit::perHour(10)->by("report-user-hour-{$userId}"),
                Limit::perDay(50)->by("report-user-day-{$userId}"),
            ];
        });

        // Share link access rate limiting
        RateLimiter::for('share-access', function (Request $request) {
            return [
                // Public share access limits
                Limit::perMinute(50)->by($request->ip()),
                Limit::perHour(500)->by("share-hour-{$request->ip()}"),
            ];
        });

        // Health check rate limiting
        RateLimiter::for('health-check', function (Request $request) {
            return [
                // Allow frequent health checks but prevent abuse
                Limit::perMinute(60)->by($request->ip()),
            ];
        });

        // Development/Debug rate limiting (only in non-production)
        if (!app()->environment('production')) {
            RateLimiter::for('debug-operations', function (Request $request) {
                return [
                    // More relaxed limits for development
                    Limit::perMinute(200)->by("debug-{$request->ip()}"),
                ];
            });
        }
    }
}