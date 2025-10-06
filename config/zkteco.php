<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZKTeco API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for ZKTeco API endpoints
    |
    */

    'api_url' => env('ZKTECO_API_URL', 'http://hcm.local/api'),
    'api_key' => env('ZKTECO_API_KEY', 'zkteco-secure-api-key-2024'),
    
    /*
    |--------------------------------------------------------------------------
    | API Settings
    |--------------------------------------------------------------------------
    */
    
    'rate_limit' => env('ZKTECO_RATE_LIMIT', 100), // requests per minute
    'timeout' => env('ZKTECO_TIMEOUT', 30), // seconds
    'max_payload_size' => env('ZKTECO_MAX_PAYLOAD_SIZE', 10485760), // 10MB in bytes
    
    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    */
    
    'sync_interval' => env('ZKTECO_SYNC_INTERVAL', 5), // minutes
    'batch_size' => env('ZKTECO_BATCH_SIZE', 100), // records per batch
    'retry_attempts' => env('ZKTECO_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('ZKTECO_RETRY_DELAY', 60), // seconds
    
    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    */
    
    'log_requests' => env('ZKTECO_LOG_REQUESTS', true),
    'log_responses' => env('ZKTECO_LOG_RESPONSES', true),
    'log_errors' => env('ZKTECO_LOG_ERRORS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Processing Settings
    |--------------------------------------------------------------------------
    */
    
    'auto_process_attendance' => env('ZKTECO_AUTO_PROCESS_ATTENDANCE', true),
    'process_duplicates' => env('ZKTECO_PROCESS_DUPLICATES', false),
    'validate_punch_codes' => env('ZKTECO_VALIDATE_PUNCH_CODES', true),
];
