<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Queue Connection Name
    |--------------------------------------------------------------------------
    |
    | Laravel's queue API supports an assortment of back-ends via a single
    | API, giving you convenient access to each back-end using the same
    | syntax for every one. Here you may define a default connection.
    |
    */

    'default' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue Connections
    |--------------------------------------------------------------------------
    |
    | Here you may configure the connection information for each server that
    | is used by your application. A default configuration has been added
    | for each back-end shipped with Laravel. You are free to add more.
    |
    | Drivers: "sync", "database", "beanstalkd", "sqs", "redis", "null"
    |
    */

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        'database' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 300, // 5 minutes
            'after_commit' => false,
        ],

        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => 'localhost',
            'queue' => 'default',
            'retry_after' => 300,
            'block_for' => 0,
            'after_commit' => false,
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
            'queue' => env('SQS_QUEUE', 'default'),
            'suffix' => env('SQS_SUFFIX'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'after_commit' => false,
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => 300,
            'block_for' => null,
            'after_commit' => false,
        ],

        /*
        |--------------------------------------------------------------------------
        | Cat Document Simplifier Queue Connections
        |--------------------------------------------------------------------------
        |
        | Custom queue configurations for the Cat Document Simplifier application.
        | These queues are optimized for different types of processing tasks.
        |
        */

        // High priority queue for GPT-4 requests and urgent processing
        'ai-priority' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'ai-priority',
            'retry_after' => 600, // 10 minutes for complex AI processing
            'after_commit' => false,
        ],

        // Default queue for regular AI processing (GPT-3.5)
        'ai-default' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'ai-default',
            'retry_after' => 300, // 5 minutes
            'after_commit' => false,
        ],

        // Queue for heavy document processing (large files)
        'heavy' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'heavy',
            'retry_after' => 900, // 15 minutes for large document processing
            'after_commit' => false,
        ],

        // Queue for lightweight document processing
        'default' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'default',
            'retry_after' => 180, // 3 minutes for quick processing
            'after_commit' => false,
        ],

        // Queue for email notifications and other lightweight tasks
        'notifications' => [
            'driver' => 'database',
            'table' => 'jobs',
            'queue' => 'notifications',
            'retry_after' => 90, // 1.5 minutes
            'after_commit' => false,
        ],

        // Redis configuration for production environments
        'redis-priority' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'ai-priority',
            'retry_after' => 600,
            'block_for' => 5,
            'after_commit' => false,
        ],

        'redis-default' => [
            'driver' => 'redis',
            'connection' => 'default',
            'queue' => 'ai-default',
            'retry_after' => 300,
            'block_for' => 5,
            'after_commit' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Job Batching
    |--------------------------------------------------------------------------
    |
    | The following options configure the database and table that store job
    | batching information. These options can be updated to any database
    | connection and table which has been defined by your application.
    |
    */

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Failed Queue Jobs
    |--------------------------------------------------------------------------
    |
    | These options configure the behavior of failed queue job logging so you
    | can control which database and table are used to store the jobs that
    | have failed. You may change them to any database / table you wish.
    |
    */

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Middleware
    |--------------------------------------------------------------------------
    |
    | Queue middleware provide a way to wrap the execution of queue jobs in
    | additional functionality. You may define middleware here that will be
    | applied to all jobs, or you can define job-specific middleware in the
    | job classes themselves.
    |
    */

    'middleware' => [
        'throttle' => [
            'ai-requests' => \Illuminate\Queue\Middleware\RateLimited::class.':ai-requests,60,5',
        ],
    ],

];