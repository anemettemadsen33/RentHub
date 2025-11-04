<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Strategy Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching strategies for different parts of the application
    |
    */

    'strategies' => [
        'api_responses' => [
            'enabled' => env('CACHE_API_RESPONSES', true),
            'ttl' => env('CACHE_API_TTL', 3600), // 1 hour
            'driver' => 'redis',
            'tags' => ['api', 'responses'],
        ],

        'database_queries' => [
            'enabled' => env('CACHE_DB_QUERIES', true),
            'ttl' => env('CACHE_DB_TTL', 600), // 10 minutes
            'driver' => 'redis',
            'tags' => ['database', 'queries'],
        ],

        'page_cache' => [
            'enabled' => env('CACHE_PAGES', true),
            'ttl' => env('CACHE_PAGE_TTL', 1800), // 30 minutes
            'driver' => 'redis',
            'tags' => ['pages'],
        ],

        'fragment_cache' => [
            'enabled' => env('CACHE_FRAGMENTS', true),
            'ttl' => env('CACHE_FRAGMENT_TTL', 900), // 15 minutes
            'driver' => 'redis',
            'tags' => ['fragments'],
        ],

        'cdn_cache' => [
            'enabled' => env('CDN_CACHE_ENABLED', true),
            'ttl' => env('CDN_CACHE_TTL', 86400), // 24 hours
            'paths' => [
                '/assets/*',
                '/images/*',
                '/js/*',
                '/css/*',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Invalidation Rules
    |--------------------------------------------------------------------------
    */

    'invalidation' => [
        'models' => [
            'Property' => ['properties', 'api', 'pages'],
            'Booking' => ['bookings', 'api', 'calendar'],
            'User' => ['users', 'api'],
            'Review' => ['reviews', 'api', 'ratings'],
        ],

        'events' => [
            'property.updated' => ['properties', 'search'],
            'booking.created' => ['bookings', 'calendar', 'availability'],
            'review.created' => ['reviews', 'ratings', 'properties'],
            'user.updated' => ['users', 'profile'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming
    |--------------------------------------------------------------------------
    */

    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', true),
        'schedule' => '0 */6 * * *', // Every 6 hours
        
        'routes' => [
            '/api/properties/featured',
            '/api/properties/popular',
            '/api/reviews/recent',
            '/api/locations/popular',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Compression
    |--------------------------------------------------------------------------
    */

    'compression' => [
        'enabled' => env('RESPONSE_COMPRESSION', true),
        'algorithm' => env('COMPRESSION_ALGORITHM', 'gzip'), // gzip, brotli
        'level' => env('COMPRESSION_LEVEL', 6), // 1-9
        'min_size' => 1024, // Minimum response size to compress (bytes)
        
        'mime_types' => [
            'application/json',
            'application/xml',
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Browser Cache Headers
    |--------------------------------------------------------------------------
    */

    'browser_cache' => [
        'static_assets' => [
            'max_age' => 31536000, // 1 year
            'paths' => ['/assets/*', '/images/*'],
        ],
        
        'dynamic_content' => [
            'max_age' => 3600, // 1 hour
            'paths' => ['/api/*'],
        ],
        
        'no_cache' => [
            'paths' => ['/admin/*', '/auth/*'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    */

    'query_optimization' => [
        'eager_loading' => [
            'enabled' => true,
            'relations' => [
                'Property' => ['owner', 'amenities', 'images', 'location'],
                'Booking' => ['property', 'user', 'payments'],
                'Review' => ['user', 'property', 'booking'],
            ],
        ],

        'chunk_size' => 1000,
        'pagination_default' => 20,
        'pagination_max' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Connection Pooling
    |--------------------------------------------------------------------------
    */

    'connection_pooling' => [
        'database' => [
            'min_connections' => env('DB_MIN_CONNECTIONS', 5),
            'max_connections' => env('DB_MAX_CONNECTIONS', 20),
            'idle_timeout' => 300, // seconds
        ],

        'redis' => [
            'min_connections' => env('REDIS_MIN_CONNECTIONS', 2),
            'max_connections' => env('REDIS_MAX_CONNECTIONS', 10),
            'idle_timeout' => 300,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Read Replicas Configuration
    |--------------------------------------------------------------------------
    */

    'read_replicas' => [
        'enabled' => env('DB_READ_REPLICAS_ENABLED', false),
        
        'connections' => [
            [
                'host' => env('DB_READ_HOST_1'),
                'weight' => 50,
            ],
            [
                'host' => env('DB_READ_HOST_2'),
                'weight' => 50,
            ],
        ],

        'strategy' => 'round-robin', // round-robin, weighted, least-connections
    ],
];
