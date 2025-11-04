<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching, query optimization, and compression.
    |
    */

    'cache' => [
        'enabled' => env('CACHE_ENABLED', true),
        'default_ttl' => env('CACHE_DEFAULT_TTL', 3600),
        'query_cache_ttl' => env('CACHE_QUERY_TTL', 600),
        'page_cache_ttl' => env('CACHE_PAGE_TTL', 1800),
        'fragment_cache_ttl' => env('CACHE_FRAGMENT_TTL', 900),
    ],

    'compression' => [
        'enabled' => env('COMPRESSION_ENABLED', true),
        'brotli' => env('COMPRESSION_BROTLI', true),
        'gzip' => env('COMPRESSION_GZIP', true),
        'level' => env('COMPRESSION_LEVEL', 6), // 1-9
    ],

    'query_optimization' => [
        'enabled' => env('QUERY_OPTIMIZATION', true),
        'eager_loading' => env('EAGER_LOADING', true),
        'chunk_size' => env('QUERY_CHUNK_SIZE', 1000),
        'log_slow_queries' => env('LOG_SLOW_QUERIES', true),
        'slow_query_threshold' => env('SLOW_QUERY_THRESHOLD', 100), // ms
    ],

    'database' => [
        'connection_pool' => env('DB_CONNECTION_POOL', true),
        'max_connections' => env('DB_MAX_CONNECTIONS', 100),
        'read_write_split' => env('DB_READ_WRITE_SPLIT', false),
    ],

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL', null),
        'assets_path' => env('CDN_ASSETS_PATH', '/assets'),
    ],

    'browser_cache' => [
        'enabled' => env('BROWSER_CACHE_ENABLED', true),
        'max_age' => env('BROWSER_CACHE_MAX_AGE', 86400), // 1 day
        'static_assets_max_age' => env('STATIC_CACHE_MAX_AGE', 31536000), // 1 year
    ],

];
