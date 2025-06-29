<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    */
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-3.5-turbo'),
        'max_tokens' => env('OPENAI_MAX_TOKENS', 2000),
        'temperature' => env('OPENAI_TEMPERATURE', 0.7),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'uploads' => [
        'max_file_size' => env('MAX_FILE_SIZE', 10240), // KB
        'supported_formats' => explode(',', env('SUPPORTED_FORMATS', 'pdf,docx,pptx')),
        'storage_path' => 'documents',
        'temp_path' => 'temp',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cat Simplification Settings
    |--------------------------------------------------------------------------
    */
    'simplification' => [
        'reading_level' => env('READING_LEVEL', 'elementary'),
        'cat_personality' => env('CAT_PERSONALITY', 'friendly'),
        'max_output_length' => env('MAX_OUTPUT_LENGTH', 1500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Processing Configuration
    |--------------------------------------------------------------------------
    */
    'processing' => [
        'queue_timeout' => env('PROCESSING_TIMEOUT', 300), // seconds
        'retry_attempts' => env('RETRY_ATTEMPTS', 3),
        'cleanup_after_days' => env('CLEANUP_AFTER_DAYS', 30),
    ],
];