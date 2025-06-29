<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application for the Cat Document Simplifier.
    | This value is used when the framework needs to place the application's
    | name in a notification or any other location as required by the application.
    |
    */

    'name' => env('APP_NAME', 'Cat Document Simplifier'),

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | These options configure the OpenAI API integration for generating
    | cat story simplifications. Make sure to set your API key in the
    | environment variables for security.
    |
    */

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'timeout' => env('OPENAI_TIMEOUT', 120),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 4000),
        'gpt4_enabled' => env('OPENAI_GPT4_ENABLED', true),
        'rate_limit' => [
            'requests_per_minute' => env('OPENAI_REQUESTS_PER_MINUTE', 20),
            'tokens_per_minute' => env('OPENAI_TOKENS_PER_MINUTE', 40000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | These options control file upload behavior including size limits,
    | supported formats, and storage configuration.
    |
    */

    'uploads' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB (10MB default)
        'storage_path' => env('UPLOAD_STORAGE_PATH', 'documents'),
        'supported_formats' => ['pdf', 'docx', 'pptx'],
        'virus_scan_enabled' => env('VIRUS_SCAN_ENABLED', false),
        'content_validation' => [
            'min_word_count' => 10,
            'min_characters' => 50,
            'max_content_length' => 100000, // characters
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Limits
    |--------------------------------------------------------------------------
    |
    | These settings control user limitations and quotas to prevent abuse
    | and manage resource usage across the application.
    |
    */

    'limits' => [
        'documents_per_user' => env('USER_DOCUMENT_LIMIT', 100),
        'total_file_size_per_user' => env('USER_FILE_SIZE_LIMIT', 104857600), // bytes (100MB)
        'simplifications_per_hour' => env('USER_SIMPLIFICATIONS_PER_HOUR', 10),
        'concurrent_processing' => env('USER_CONCURRENT_PROCESSING', 5),
        'daily_ai_requests' => env('USER_DAILY_AI_REQUESTS', 50),
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    |
    | Settings that control document processing, queue management,
    | and background job execution.
    |
    */

    'processing' => [
        'default_queue' => env('DEFAULT_PROCESSING_QUEUE', 'default'),
        'heavy_file_threshold' => env('HEAVY_FILE_THRESHOLD', 5242880), // bytes (5MB)
        'processing_timeout' => [
            'document_extraction' => env('DOCUMENT_PROCESSING_TIMEOUT', 300), // seconds
            'ai_generation' => env('AI_PROCESSING_TIMEOUT', 180), // seconds
        ],
        'retry_attempts' => [
            'document_processing' => 3,
            'ai_processing' => 3,
        ],
        'cleanup' => [
            'failed_jobs_retention_days' => env('FAILED_JOBS_RETENTION', 30),
            'processed_documents_archive_days' => env('DOCUMENT_ARCHIVE_DAYS', 90),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Model Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for different AI models and their specific configurations
    | including pricing, limits, and feature availability.
    |
    */

    'ai_models' => [
        'gpt-3.5-turbo' => [
            'enabled' => true,
            'max_tokens' => 4096,
            'cost_per_1k_input_tokens' => 0.0015,
            'cost_per_1k_output_tokens' => 0.002,
            'recommended_for' => ['basic', 'intermediate'],
        ],
        'gpt-4' => [
            'enabled' => env('GPT4_ENABLED', true),
            'max_tokens' => 8192,
            'cost_per_1k_input_tokens' => 0.03,
            'cost_per_1k_output_tokens' => 0.06,
            'recommended_for' => ['intermediate', 'advanced'],
            'requires_premium' => false, // Set to true if premium feature
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Complexity Levels
    |--------------------------------------------------------------------------
    |
    | Configuration for different complexity levels and their characteristics
    | used in the simplification process.
    |
    */

    'complexity_levels' => [
        'basic' => [
            'name' => 'Basic',
            'description' => 'Simple language for young learners (ages 5-8)',
            'target_reading_level' => 'Elementary',
            'max_words_per_sentence' => 10,
            'vocabulary_level' => 'basic',
            'cat_metaphor_intensity' => 'high',
        ],
        'intermediate' => [
            'name' => 'Intermediate',
            'description' => 'Clear explanations for middle school students (ages 9-14)',
            'target_reading_level' => 'Middle School',
            'max_words_per_sentence' => 15,
            'vocabulary_level' => 'intermediate',
            'cat_metaphor_intensity' => 'medium',
        ],
        'advanced' => [
            'name' => 'Advanced',
            'description' => 'Sophisticated language for high school and adults',
            'target_reading_level' => 'High School+',
            'max_words_per_sentence' => 20,
            'vocabulary_level' => 'advanced',
            'cat_metaphor_intensity' => 'medium',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quality Metrics
    |--------------------------------------------------------------------------
    |
    | Configuration for quality assessment and scoring of generated
    | simplifications including thresholds and weights.
    |
    */

    'quality_metrics' => [
        'readability' => [
            'min_score' => 3,
            'target_score' => 7,
            'weight' => 0.3,
        ],
        'coherence' => [
            'min_score' => 0.6,
            'target_score' => 0.8,
            'weight' => 0.25,
        ],
        'engagement' => [
            'min_score' => 0.5,
            'target_score' => 0.8,
            'weight' => 0.2,
        ],
        'cat_theme_consistency' => [
            'min_score' => 0.6,
            'target_score' => 0.9,
            'weight' => 0.25,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    |
    | Feature toggles for enabling/disabling specific functionality
    | in the application for testing or gradual rollouts.
    |
    */

    'features' => [
        'public_sharing' => env('FEATURE_PUBLIC_SHARING', true),
        'document_collaboration' => env('FEATURE_COLLABORATION', false),
        'advanced_analytics' => env('FEATURE_ANALYTICS', true),
        'email_notifications' => env('FEATURE_EMAIL_NOTIFICATIONS', true),
        'batch_processing' => env('FEATURE_BATCH_PROCESSING', true),
        'api_access' => env('FEATURE_API_ACCESS', false),
        'custom_prompts' => env('FEATURE_CUSTOM_PROMPTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for various notification channels and their settings
    | including email templates and delivery preferences.
    |
    */

    'notifications' => [
        'email' => [
            'enabled' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
            'from_address' => env('MAIL_FROM_ADDRESS', 'noreply@catdocs.com'),
            'from_name' => env('MAIL_FROM_NAME', 'Cat Document Simplifier'),
            'templates' => [
                'processing_complete' => 'emails.processing-complete',
                'processing_failed' => 'emails.processing-failed',
                'weekly_summary' => 'emails.weekly-summary',
            ],
        ],
        'in_app' => [
            'enabled' => true,
            'retention_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for caching various components of the application to improve
    | performance and reduce API calls.
    |
    */

    'cache' => [
        'user_stats_ttl' => env('CACHE_USER_STATS_TTL', 3600), // 1 hour
        'document_stats_ttl' => env('CACHE_DOCUMENT_STATS_TTL', 1800), // 30 minutes
        'dashboard_data_ttl' => env('CACHE_DASHBOARD_TTL', 900), // 15 minutes
        'public_content_ttl' => env('CACHE_PUBLIC_CONTENT_TTL', 86400), // 24 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Security-related configuration including rate limiting, content filtering,
    | and access controls.
    |
    */

    'security' => [
        'rate_limiting' => [
            'upload_attempts_per_hour' => 20,
            'api_requests_per_minute' => 60,
            'login_attempts_per_minute' => 5,
        ],
        'content_filtering' => [
            'enabled' => true,
            'blocked_patterns' => [
                '/ignore.*previous.*instructions?/i',
                '/bypass.*safety/i',
                '/act\s+as.*(?:jailbreak|dan|evil)/i',
            ],
        ],
        'file_validation' => [
            'strict_mime_checking' => true,
            'scan_for_macros' => env('SCAN_FOR_MACROS', true),
            'max_embedded_files' => 5,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for analytics tracking and reporting to monitor application
    | usage and performance metrics.
    |
    */

    'analytics' => [
        'enabled' => env('ANALYTICS_ENABLED', true),
        'track_user_actions' => true,
        'track_processing_metrics' => true,
        'track_quality_scores' => true,
        'retention_days' => env('ANALYTICS_RETENTION_DAYS', 365),
        'anonymize_user_data' => env('ANALYTICS_ANONYMIZE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Development & Debug Settings
    |--------------------------------------------------------------------------
    |
    | Configuration options specific to development and debugging environments.
    | These should be disabled in production.
    |
    */

    'debug' => [
        'log_ai_requests' => env('DEBUG_LOG_AI_REQUESTS', false),
        'log_processing_details' => env('DEBUG_LOG_PROCESSING', false),
        'save_failed_content' => env('DEBUG_SAVE_FAILED_CONTENT', false),
        'mock_ai_responses' => env('DEBUG_MOCK_AI', false),
        'bypass_rate_limits' => env('DEBUG_BYPASS_LIMITS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup & Archival
    |--------------------------------------------------------------------------
    |
    | Settings for automated backup and archival of user data and documents
    | to ensure data preservation and compliance.
    |
    */

    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'frequency' => env('BACKUP_FREQUENCY', 'daily'),
        'retention_days' => env('BACKUP_RETENTION_DAYS', 90),
        'include_documents' => env('BACKUP_INCLUDE_DOCUMENTS', true),
        'include_user_data' => env('BACKUP_INCLUDE_USER_DATA', true),
        'compress_backups' => env('BACKUP_COMPRESS', true),
    ],

];