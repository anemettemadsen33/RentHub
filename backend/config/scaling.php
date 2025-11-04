<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Load Balancing Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for load balancing across multiple application instances
    |
    */

    'load_balancer' => [
        'enabled' => env('LOAD_BALANCER_ENABLED', false),
        'algorithm' => env('LOAD_BALANCER_ALGORITHM', 'round_robin'), // round_robin, least_connections, ip_hash
        'health_check_interval' => env('LOAD_BALANCER_HEALTH_CHECK', 30), // seconds
        'nodes' => [
            [
                'host' => env('APP_NODE_1_HOST', '10.0.1.10'),
                'port' => env('APP_NODE_1_PORT', 80),
                'weight' => env('APP_NODE_1_WEIGHT', 1),
            ],
            [
                'host' => env('APP_NODE_2_HOST', '10.0.1.11'),
                'port' => env('APP_NODE_2_PORT', 80),
                'weight' => env('APP_NODE_2_WEIGHT', 1),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Scaling Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic scaling based on metrics
    |
    */

    'auto_scaling' => [
        'enabled' => env('AUTO_SCALING_ENABLED', false),
        'provider' => env('AUTO_SCALING_PROVIDER', 'aws'), // aws, azure, gcp
        'min_instances' => env('AUTO_SCALING_MIN_INSTANCES', 2),
        'max_instances' => env('AUTO_SCALING_MAX_INSTANCES', 10),

        'metrics' => [
            'cpu' => [
                'target' => env('AUTO_SCALING_CPU_TARGET', 70), // percentage
                'scale_up_threshold' => 80,
                'scale_down_threshold' => 30,
            ],
            'memory' => [
                'target' => env('AUTO_SCALING_MEMORY_TARGET', 75), // percentage
                'scale_up_threshold' => 85,
                'scale_down_threshold' => 40,
            ],
            'requests' => [
                'target' => env('AUTO_SCALING_REQUESTS_TARGET', 1000), // per minute
                'scale_up_threshold' => 1500,
                'scale_down_threshold' => 500,
            ],
        ],

        'cooldown' => [
            'scale_up' => env('AUTO_SCALING_COOLDOWN_UP', 300), // seconds
            'scale_down' => env('AUTO_SCALING_COOLDOWN_DOWN', 600), // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Replication Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for read replicas and database replication
    |
    */

    'database_replication' => [
        'enabled' => env('DB_REPLICATION_ENABLED', false),

        'read_replicas' => [
            [
                'host' => env('DB_READ_REPLICA_1_HOST', '127.0.0.1'),
                'port' => env('DB_READ_REPLICA_1_PORT', 3306),
                'database' => env('DB_READ_REPLICA_1_DATABASE', 'renthub'),
                'username' => env('DB_READ_REPLICA_1_USERNAME', 'root'),
                'password' => env('DB_READ_REPLICA_1_PASSWORD', ''),
                'weight' => env('DB_READ_REPLICA_1_WEIGHT', 1),
            ],
            [
                'host' => env('DB_READ_REPLICA_2_HOST', '127.0.0.1'),
                'port' => env('DB_READ_REPLICA_2_PORT', 3306),
                'database' => env('DB_READ_REPLICA_2_DATABASE', 'renthub'),
                'username' => env('DB_READ_REPLICA_2_USERNAME', 'root'),
                'password' => env('DB_READ_REPLICA_2_PASSWORD', ''),
                'weight' => env('DB_READ_REPLICA_2_WEIGHT', 1),
            ],
        ],

        'write_master' => [
            'host' => env('DB_WRITE_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('DB_WRITE_PORT', env('DB_PORT', 3306)),
            'database' => env('DB_WRITE_DATABASE', env('DB_DATABASE', 'renthub')),
            'username' => env('DB_WRITE_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('DB_WRITE_PASSWORD', env('DB_PASSWORD', '')),
        ],

        'sticky_sessions' => env('DB_STICKY_SESSIONS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Microservices Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for microservices architecture
    |
    */

    'microservices' => [
        'enabled' => env('MICROSERVICES_ENABLED', false),
        'gateway_url' => env('MICROSERVICES_GATEWAY_URL', 'http://localhost:8080'),

        'services' => [
            'auth' => [
                'url' => env('MICROSERVICE_AUTH_URL', 'http://auth-service:8001'),
                'timeout' => 5,
                'retry' => 3,
            ],
            'properties' => [
                'url' => env('MICROSERVICE_PROPERTIES_URL', 'http://properties-service:8002'),
                'timeout' => 10,
                'retry' => 2,
            ],
            'bookings' => [
                'url' => env('MICROSERVICE_BOOKINGS_URL', 'http://bookings-service:8003'),
                'timeout' => 8,
                'retry' => 3,
            ],
            'payments' => [
                'url' => env('MICROSERVICE_PAYMENTS_URL', 'http://payments-service:8004'),
                'timeout' => 15,
                'retry' => 3,
            ],
            'notifications' => [
                'url' => env('MICROSERVICE_NOTIFICATIONS_URL', 'http://notifications-service:8005'),
                'timeout' => 5,
                'retry' => 2,
            ],
        ],

        'circuit_breaker' => [
            'enabled' => true,
            'failure_threshold' => 5,
            'timeout' => 60, // seconds
            'retry_timeout' => 30, // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Scaling Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for distributed caching
    |
    */

    'cache_scaling' => [
        'strategy' => env('CACHE_SCALING_STRATEGY', 'redis_cluster'), // redis_cluster, memcached_pool

        'redis_cluster' => [
            'nodes' => [
                env('REDIS_CLUSTER_NODE_1', '127.0.0.1:6379'),
                env('REDIS_CLUSTER_NODE_2', '127.0.0.1:6380'),
                env('REDIS_CLUSTER_NODE_3', '127.0.0.1:6381'),
            ],
            'options' => [
                'cluster' => 'redis',
                'prefix' => env('REDIS_PREFIX', 'renthub_cache:'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Scaling Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for queue workers scaling
    |
    */

    'queue_scaling' => [
        'enabled' => env('QUEUE_SCALING_ENABLED', false),
        'driver' => env('QUEUE_DRIVER', 'redis'),

        'workers' => [
            'min' => env('QUEUE_WORKERS_MIN', 1),
            'max' => env('QUEUE_WORKERS_MAX', 10),
            'auto_scale' => true,
            'scale_up_threshold' => 100, // jobs in queue
            'scale_down_threshold' => 10,
        ],

        'supervisor' => [
            'enabled' => env('SUPERVISOR_ENABLED', true),
            'command' => 'php artisan queue:work',
            'processes' => env('SUPERVISOR_PROCESSES', 3),
            'max_time' => 3600,
            'max_jobs' => 1000,
            'memory_limit' => 512,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for application health checks
    |
    */

    'health_check' => [
        'enabled' => env('HEALTH_CHECK_ENABLED', true),
        'endpoint' => env('HEALTH_CHECK_ENDPOINT', '/health'),

        'checks' => [
            'database' => true,
            'redis' => true,
            'queue' => true,
            'storage' => true,
            'external_services' => true,
        ],

        'thresholds' => [
            'response_time' => 1000, // milliseconds
            'memory_usage' => 90, // percentage
            'disk_usage' => 85, // percentage
        ],
    ],

];
