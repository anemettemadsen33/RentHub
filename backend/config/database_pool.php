<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Connection Pool Configuration
    |--------------------------------------------------------------------------
    |
    | These settings control the connection pooling behavior for database
    | connections. Connection pooling helps reduce the overhead of creating
    | new connections and improves application performance.
    |
    */

    'pool' => [
        
        /*
        |--------------------------------------------------------------------------
        | Maximum Pool Size
        |--------------------------------------------------------------------------
        |
        | The maximum number of connections that can be maintained in the pool
        | for each database connection. When this limit is reached, new
        | connections will be created on-demand but not pooled.
        |
        */
        'max_connections' => env('DB_POOL_MAX_CONNECTIONS', 20),

        /*
        |--------------------------------------------------------------------------
        | Minimum Pool Size
        |--------------------------------------------------------------------------
        |
        | The minimum number of connections to maintain in the pool. These
        | connections are pre-warmed during application startup to reduce
               | connection acquisition time.
        |
        */
        'min_connections' => env('DB_POOL_MIN_CONNECTIONS', 5),

        /*
        |--------------------------------------------------------------------------
        | Connection Timeout
        |--------------------------------------------------------------------------
        |
        | Maximum time (in seconds) to wait for a connection from the pool
        | before timing out. This prevents applications from hanging
        | indefinitely when all connections are in use.
        |
        */
        'connection_timeout' => env('DB_POOL_CONNECTION_TIMEOUT', 30),

        /*
        |--------------------------------------------------------------------------
        | Idle Timeout
        |--------------------------------------------------------------------------
        |
        | Maximum time (in seconds) a connection can be idle in the pool
        | before being considered expired and closed. This helps prevent
        | resource leaks and stale connections.
        |
        */
        'idle_timeout' => env('DB_POOL_IDLE_TIMEOUT', 300),

        /*
        |--------------------------------------------------------------------------
        | Connection Health Check
        |--------------------------------------------------------------------------
        |
        | Enable periodic health checks for pooled connections to ensure
        | they are still valid and responsive. Expired connections are
        | automatically removed from the pool.
        |
        */
        'health_check_enabled' => env('DB_POOL_HEALTH_CHECK_ENABLED', true),

        /*
        |--------------------------------------------------------------------------
        | Health Check Interval
        |--------------------------------------------------------------------------
        |
        | Interval (in seconds) between connection health checks.
        | Lower values provide more frequent checks but consume more resources.
        |
        */
        'health_check_interval' => env('DB_POOL_HEALTH_CHECK_INTERVAL', 60),

        /*
        |--------------------------------------------------------------------------
        | Query Retry Configuration
        |--------------------------------------------------------------------------
        |
        | Configuration for automatic retry of failed database queries.
        | This helps handle temporary connection issues and improves reliability.
        |
        */
        'retry' => [
            'max_attempts' => env('DB_POOL_RETRY_ATTEMPTS', 3),
            'delay_ms' => env('DB_POOL_RETRY_DELAY_MS', 100),
            'backoff_multiplier' => env('DB_POOL_RETRY_BACKOFF_MULTIPLIER', 2),
            'max_delay_ms' => env('DB_POOL_RETRY_MAX_DELAY_MS', 1000),
        ],

        /*
        |--------------------------------------------------------------------------
        | Slow Query Logging
        |--------------------------------------------------------------------------
        |
        | Configuration for logging slow database queries. This helps identify
        | performance bottlenecks and optimize database operations.
        |
        */
        'slow_query' => [
            'enabled' => env('DB_POOL_SLOW_QUERY_ENABLED', true),
            'threshold_seconds' => env('DB_POOL_SLOW_QUERY_THRESHOLD', 1.0),
            'log_bindings' => env('DB_POOL_LOG_BINDINGS', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | Connection Pooling by Driver
        |--------------------------------------------------------------------------
        |
        | Enable connection pooling for specific database drivers.
        | Not all drivers support connection pooling effectively.
        |
        */
        'supported_drivers' => [
            'mysql' => env('DB_POOL_MYSQL_ENABLED', true),
            'mariadb' => env('DB_POOL_MARIADB_ENABLED', true),
            'pgsql' => env('DB_POOL_PGSQL_ENABLED', true),
            'sqlite' => env('DB_POOL_SQLITE_ENABLED', false),
        ],

        /*
        |--------------------------------------------------------------------------
        | Performance Monitoring
        |--------------------------------------------------------------------------
        |
        | Enable detailed performance monitoring and metrics collection
        | for connection pool operations. This helps track pool efficiency
        | and identify optimization opportunities.
        |
        */
        'monitoring' => [
            'enabled' => env('DB_POOL_MONITORING_ENABLED', true),
            'metrics_retention_hours' => env('DB_POOL_METRICS_RETENTION', 24),
            'log_stats_interval' => env('DB_POOL_STATS_LOG_INTERVAL', 300),
        ],
    ],

];