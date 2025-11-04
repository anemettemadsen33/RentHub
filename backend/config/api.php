<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | API Versioning
    |--------------------------------------------------------------------------
    */
    
    'versioning' => [
        'enabled' => env('API_VERSIONING_ENABLED', true),
        'default' => env('API_DEFAULT_VERSION', 'v1'),
        'prefix' => 'api',
        'header' => 'X-API-Version',
        'query_param' => 'version',
        
        'supported_versions' => ['v1', 'v2'],
        
        'deprecation' => [
            'enabled' => true,
            'header' => 'X-API-Deprecation',
            'sunset_header' => 'Sunset',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GraphQL Configuration
    |--------------------------------------------------------------------------
    */
    
    'graphql' => [
        'enabled' => env('GRAPHQL_ENABLED', true),
        'route' => 'graphql',
        'playground_enabled' => env('GRAPHQL_PLAYGROUND', true),
        
        'schemas' => [
            'default' => [
                'query' => [
                    'properties' => \App\GraphQL\Queries\PropertiesQuery::class,
                    'property' => \App\GraphQL\Queries\PropertyQuery::class,
                    'bookings' => \App\GraphQL\Queries\BookingsQuery::class,
                ],
                'mutation' => [
                    'createProperty' => \App\GraphQL\Mutations\CreatePropertyMutation::class,
                    'updateProperty' => \App\GraphQL\Mutations\UpdatePropertyMutation::class,
                    'createBooking' => \App\GraphQL\Mutations\CreateBookingMutation::class,
                ],
                'types' => [
                    'Property' => \App\GraphQL\Types\PropertyType::class,
                    'Booking' => \App\GraphQL\Types\BookingType::class,
                    'User' => \App\GraphQL\Types\UserType::class,
                ],
            ],
        ],
        
        'security' => [
            'max_query_depth' => 10,
            'max_query_complexity' => 1000,
            'disable_introspection' => env('GRAPHQL_DISABLE_INTROSPECTION', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WebSocket Configuration
    |--------------------------------------------------------------------------
    */
    
    'websocket' => [
        'enabled' => env('WEBSOCKET_ENABLED', true),
        'host' => env('WEBSOCKET_HOST', '0.0.0.0'),
        'port' => env('WEBSOCKET_PORT', 6001),
        'ssl' => env('WEBSOCKET_SSL', false),
        
        'channels' => [
            'booking_updates' => true,
            'property_views' => true,
            'messages' => true,
            'notifications' => true,
        ],
        
        'redis' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Background Jobs Configuration
    |--------------------------------------------------------------------------
    */
    
    'jobs' => [
        'default_queue' => env('QUEUE_CONNECTION', 'redis'),
        
        'queues' => [
            'high' => ['priority' => 10, 'timeout' => 60],
            'default' => ['priority' => 5, 'timeout' => 300],
            'low' => ['priority' => 1, 'timeout' => 600],
        ],
        
        'workers' => [
            'high' => 3,
            'default' => 2,
            'low' => 1,
        ],
        
        'retry' => [
            'times' => 3,
            'delay' => 60,
        ],
        
        'optimization' => [
            'batch_processing' => true,
            'chunk_size' => 100,
            'horizon_enabled' => env('HORIZON_ENABLED', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Sharding
    |--------------------------------------------------------------------------
    */
    
    'sharding' => [
        'enabled' => env('DB_SHARDING_ENABLED', false),
        'strategy' => 'hash', // hash, range, list
        'shard_key' => 'user_id',
        
        'shards' => [
            'shard_1' => [
                'host' => env('DB_SHARD_1_HOST', '127.0.0.1'),
                'database' => env('DB_SHARD_1_DATABASE', 'renthub_shard_1'),
                'range' => [0, 1000000],
            ],
            'shard_2' => [
                'host' => env('DB_SHARD_2_HOST', '127.0.0.1'),
                'database' => env('DB_SHARD_2_DATABASE', 'renthub_shard_2'),
                'range' => [1000001, 2000000],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Full-Text Search
    |--------------------------------------------------------------------------
    */
    
    'search' => [
        'driver' => env('SEARCH_DRIVER', 'meilisearch'), // elasticsearch, meilisearch, algolia
        
        'meilisearch' => [
            'host' => env('MEILISEARCH_HOST', 'http://127.0.0.1:7700'),
            'key' => env('MEILISEARCH_KEY'),
            
            'indexes' => [
                'properties' => [
                    'searchable' => ['title', 'description', 'address', 'city'],
                    'filterable' => ['type', 'price', 'bedrooms', 'bathrooms'],
                    'sortable' => ['price', 'created_at', 'rating'],
                ],
                'users' => [
                    'searchable' => ['name', 'email', 'bio'],
                    'filterable' => ['role', 'verified'],
                ],
            ],
        ],
        
        'elasticsearch' => [
            'hosts' => [env('ELASTICSEARCH_HOST', 'localhost:9200')],
            'index_prefix' => env('ELASTICSEARCH_PREFIX', 'renthub_'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation (OpenAPI/Swagger)
    |--------------------------------------------------------------------------
    */
    
    'documentation' => [
        'enabled' => env('API_DOCS_ENABLED', true),
        'route' => 'api/documentation',
        'format' => 'openapi', // openapi, swagger
        
        'openapi' => [
            'version' => '3.0.0',
            'title' => 'RentHub API',
            'description' => 'Rental Platform API Documentation',
            'contact' => [
                'name' => 'API Support',
                'email' => 'api@renthub.com',
            ],
            'license' => [
                'name' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT',
            ],
        ],
        
        'servers' => [
            [
                'url' => env('APP_URL') . '/api/v1',
                'description' => 'Production Server',
            ],
        ],
        
        'security' => [
            'bearerAuth' => [
                'type' => 'http',
                'scheme' => 'bearer',
                'bearerFormat' => 'JWT',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Testing Configuration
    |--------------------------------------------------------------------------
    */
    
    'testing' => [
        'unit' => [
            'enabled' => true,
            'coverage_target' => 80,
            'exclude' => ['vendor', 'tests', 'storage'],
        ],
        
        'integration' => [
            'enabled' => true,
            'database' => 'testing',
        ],
        
        'e2e' => [
            'enabled' => env('E2E_TESTS_ENABLED', false),
            'browser' => 'chrome',
            'headless' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    
    'rate_limiting' => [
        'enabled' => true,
        
        'limits' => [
            'global' => [
                'max_attempts' => 60,
                'decay_minutes' => 1,
            ],
            'auth' => [
                'max_attempts' => 5,
                'decay_minutes' => 1,
            ],
            'search' => [
                'max_attempts' => 30,
                'decay_minutes' => 1,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Strategy
    |--------------------------------------------------------------------------
    */
    
    'caching' => [
        'enabled' => true,
        'driver' => env('CACHE_DRIVER', 'redis'),
        
        'ttl' => [
            'properties' => 3600,
            'users' => 1800,
            'search' => 600,
            'static' => 86400,
        ],
        
        'tags' => [
            'properties' => 'properties',
            'users' => 'users',
            'bookings' => 'bookings',
        ],
    ],

];
