<?php return array (
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => '12',
      'verify' => true,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\laragon\\www\\RentHub\\backend\\resources\\views',
    ),
    'compiled' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\framework\\views',
  ),
  'api' => 
  array (
    'versioning' => 
    array (
      'enabled' => true,
      'default' => 'v1',
      'prefix' => 'api',
      'header' => 'X-API-Version',
      'query_param' => 'version',
      'supported_versions' => 
      array (
        0 => 'v1',
        1 => 'v2',
      ),
      'deprecation' => 
      array (
        'enabled' => true,
        'header' => 'X-API-Deprecation',
        'sunset_header' => 'Sunset',
      ),
    ),
    'graphql' => 
    array (
      'enabled' => true,
      'route' => 'graphql',
      'playground_enabled' => true,
      'schemas' => 
      array (
        'default' => 
        array (
          'query' => 
          array (
            'properties' => 'App\\GraphQL\\Queries\\PropertiesQuery',
            'property' => 'App\\GraphQL\\Queries\\PropertyQuery',
            'bookings' => 'App\\GraphQL\\Queries\\BookingsQuery',
          ),
          'mutation' => 
          array (
            'createProperty' => 'App\\GraphQL\\Mutations\\CreatePropertyMutation',
            'updateProperty' => 'App\\GraphQL\\Mutations\\UpdatePropertyMutation',
            'createBooking' => 'App\\GraphQL\\Mutations\\CreateBookingMutation',
          ),
          'types' => 
          array (
            'Property' => 'App\\GraphQL\\Types\\PropertyType',
            'Booking' => 'App\\GraphQL\\Types\\BookingType',
            'User' => 'App\\GraphQL\\Types\\UserType',
          ),
        ),
      ),
      'security' => 
      array (
        'max_query_depth' => 10,
        'max_query_complexity' => 1000,
        'disable_introspection' => false,
      ),
    ),
    'websocket' => 
    array (
      'enabled' => true,
      'host' => '0.0.0.0',
      'port' => 6001,
      'ssl' => false,
      'channels' => 
      array (
        'booking_updates' => true,
        'property_views' => true,
        'messages' => true,
        'notifications' => true,
      ),
      'redis' => 
      array (
        'host' => '127.0.0.1',
        'port' => '6379',
      ),
    ),
    'jobs' => 
    array (
      'default_queue' => 'sync',
      'queues' => 
      array (
        'high' => 
        array (
          'priority' => 10,
          'timeout' => 60,
        ),
        'default' => 
        array (
          'priority' => 5,
          'timeout' => 300,
        ),
        'low' => 
        array (
          'priority' => 1,
          'timeout' => 600,
        ),
      ),
      'workers' => 
      array (
        'high' => 3,
        'default' => 2,
        'low' => 1,
      ),
      'retry' => 
      array (
        'times' => 3,
        'delay' => 60,
      ),
      'optimization' => 
      array (
        'batch_processing' => true,
        'chunk_size' => 100,
        'horizon_enabled' => true,
      ),
    ),
    'sharding' => 
    array (
      'enabled' => false,
      'strategy' => 'hash',
      'shard_key' => 'user_id',
      'shards' => 
      array (
        'shard_1' => 
        array (
          'host' => '127.0.0.1',
          'database' => 'renthub_shard_1',
          'range' => 
          array (
            0 => 0,
            1 => 1000000,
          ),
        ),
        'shard_2' => 
        array (
          'host' => '127.0.0.1',
          'database' => 'renthub_shard_2',
          'range' => 
          array (
            0 => 1000001,
            1 => 2000000,
          ),
        ),
      ),
    ),
    'search' => 
    array (
      'driver' => 'meilisearch',
      'meilisearch' => 
      array (
        'host' => 'http://127.0.0.1:7700',
        'key' => '',
        'indexes' => 
        array (
          'properties' => 
          array (
            'searchable' => 
            array (
              0 => 'title',
              1 => 'description',
              2 => 'address',
              3 => 'city',
            ),
            'filterable' => 
            array (
              0 => 'type',
              1 => 'price',
              2 => 'bedrooms',
              3 => 'bathrooms',
            ),
            'sortable' => 
            array (
              0 => 'price',
              1 => 'created_at',
              2 => 'rating',
            ),
          ),
          'users' => 
          array (
            'searchable' => 
            array (
              0 => 'name',
              1 => 'email',
              2 => 'bio',
            ),
            'filterable' => 
            array (
              0 => 'role',
              1 => 'verified',
            ),
          ),
        ),
      ),
      'elasticsearch' => 
      array (
        'hosts' => 
        array (
          0 => 'localhost:9200',
        ),
        'index_prefix' => 'renthub_',
      ),
    ),
    'documentation' => 
    array (
      'enabled' => true,
      'route' => 'api/documentation',
      'format' => 'openapi',
      'openapi' => 
      array (
        'version' => '3.0.0',
        'title' => 'RentHub API',
        'description' => 'Rental Platform API Documentation',
        'contact' => 
        array (
          'name' => 'API Support',
          'email' => 'api@renthub.com',
        ),
        'license' => 
        array (
          'name' => 'MIT',
          'url' => 'https://opensource.org/licenses/MIT',
        ),
      ),
      'servers' => 
      array (
        0 => 
        array (
          'url' => 'http://localhost:8000/api/v1',
          'description' => 'Production Server',
        ),
      ),
      'security' => 
      array (
        'bearerAuth' => 
        array (
          'type' => 'http',
          'scheme' => 'bearer',
          'bearerFormat' => 'JWT',
        ),
      ),
    ),
    'testing' => 
    array (
      'unit' => 
      array (
        'enabled' => true,
        'coverage_target' => 80,
        'exclude' => 
        array (
          0 => 'vendor',
          1 => 'tests',
          2 => 'storage',
        ),
      ),
      'integration' => 
      array (
        'enabled' => true,
        'database' => 'testing',
      ),
      'e2e' => 
      array (
        'enabled' => false,
        'browser' => 'chrome',
        'headless' => true,
      ),
    ),
    'rate_limiting' => 
    array (
      'enabled' => true,
      'limits' => 
      array (
        'global' => 
        array (
          'max_attempts' => 60,
          'decay_minutes' => 1,
        ),
        'auth' => 
        array (
          'max_attempts' => 5,
          'decay_minutes' => 1,
        ),
        'search' => 
        array (
          'max_attempts' => 30,
          'decay_minutes' => 1,
        ),
      ),
    ),
    'caching' => 
    array (
      'enabled' => true,
      'driver' => 'redis',
      'ttl' => 
      array (
        'properties' => 3600,
        'users' => 1800,
        'search' => 600,
        'static' => 86400,
      ),
      'tags' => 
      array (
        'properties' => 'properties',
        'users' => 'users',
        'bookings' => 'bookings',
      ),
    ),
  ),
  'app' => 
  array (
    'name' => 'RentHub',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost:8000',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:fodQaKMrekfeE/3vj/TdJm9+4mslWFRMLN6x9LBB5U4=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
      'store' => 'database',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\BroadcastServiceProvider',
      25 => 'App\\Providers\\DynamicConfigServiceProvider',
      26 => 'App\\Providers\\Filament\\AdminPanelProvider',
      27 => 'App\\Providers\\OptimizedAuthServiceProvider',
      28 => 'App\\Providers\\DatabaseConnectionPoolServiceProvider',
      29 => 'App\\Providers\\CircuitBreakerServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Uri' => 'Illuminate\\Support\\Uri',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'sanctum' => 
      array (
        'driver' => 'sanctum',
        'provider' => NULL,
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'backup' => 
  array (
    'enabled' => true,
    'database' => 
    array (
      'enabled' => true,
      'connections' => 
      array (
        'mysql' => 
        array (
          'driver' => 'mysql',
          'host' => '127.0.0.1',
          'port' => '3306',
          'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
          'username' => 'root',
          'password' => '',
        ),
      ),
      'backup_options' => 
      array (
        'compress' => true,
        'compression_type' => 'gzip',
        'include_routines' => true,
        'include_triggers' => true,
        'add_drop_table' => true,
        'add_drop_trigger' => true,
        'single_transaction' => true,
        'lock_tables' => false,
        'quick' => true,
        'extended_insert' => true,
      ),
      'schedule' => 
      array (
        'full_backup' => 'daily',
        'incremental_backup' => 'hourly',
        'time' => '02:00',
      ),
      'retention' => 
      array (
        'daily' => 7,
        'weekly' => 4,
        'monthly' => 3,
        'yearly' => 1,
      ),
    ),
    'files' => 
    array (
      'enabled' => true,
      'include' => 
      array (
        'storage' => 
        array (
          'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\app',
          'exclude' => 
          array (
            0 => 'cache',
            1 => 'logs',
            2 => 'framework/cache',
            3 => 'framework/sessions',
            4 => 'framework/views',
          ),
        ),
        'public' => 
        array (
          'path' => 'C:\\laragon\\www\\RentHub\\backend\\public',
          'exclude' => 
          array (
            0 => 'build',
            1 => 'hot',
          ),
        ),
        'uploads' => 
        array (
          'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\app/public',
          'exclude' => 
          array (
          ),
        ),
      ),
      'backup_options' => 
      array (
        'compress' => true,
        'compression_type' => 'tar.gz',
        'incremental' => true,
        'follow_symlinks' => false,
        'max_file_size' => 100,
      ),
      'schedule' => 
      array (
        'full_backup' => 'daily',
        'incremental_backup' => 'hourly',
        'time' => '03:00',
      ),
      'retention' => 
      array (
        'daily' => 7,
        'weekly' => 4,
        'monthly' => 3,
      ),
    ),
    'destinations' => 
    array (
      'local' => 
      array (
        'enabled' => true,
        'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\backups',
        'permissions' => 493,
      ),
      's3' => 
      array (
        'enabled' => false,
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => 'renthub-backups',
        'path' => 'backups',
        'storage_class' => 'STANDARD_IA',
      ),
      'ftp' => 
      array (
        'enabled' => false,
        'host' => NULL,
        'port' => 21,
        'username' => NULL,
        'password' => NULL,
        'root' => '/backups',
        'passive' => true,
        'ssl' => true,
      ),
      'dropbox' => 
      array (
        'enabled' => false,
        'token' => NULL,
        'path' => '/RentHub/backups',
      ),
      'google_drive' => 
      array (
        'enabled' => false,
        'client_id' => NULL,
        'client_secret' => NULL,
        'refresh_token' => NULL,
        'folder_id' => NULL,
      ),
    ),
    'notifications' => 
    array (
      'enabled' => true,
      'events' => 
      array (
        'backup_success' => true,
        'backup_failure' => true,
        'cleanup_success' => true,
        'cleanup_failure' => true,
        'restore_success' => true,
        'restore_failure' => true,
      ),
      'channels' => 
      array (
        'mail' => 
        array (
          'enabled' => true,
          'to' => 
          array (
            0 => 'admin@renthub.com',
          ),
        ),
        'slack' => 
        array (
          'enabled' => false,
          'webhook_url' => NULL,
          'channel' => '#backups',
        ),
        'discord' => 
        array (
          'enabled' => false,
          'webhook_url' => NULL,
        ),
      ),
    ),
    'testing' => 
    array (
      'enabled' => true,
      'schedule' => 'weekly',
      'tests' => 
      array (
        'database_restore' => true,
        'file_integrity' => true,
        'backup_size_check' => true,
        'backup_age_check' => true,
      ),
      'test_database' => 
      array (
        'connection' => 'mysql_test',
        'database' => 'renthub_backup_test',
      ),
      'thresholds' => 
      array (
        'max_restore_time' => 300,
        'max_backup_age' => 86400,
        'min_backup_size' => 1024,
      ),
    ),
    'disaster_recovery' => 
    array (
      'enabled' => true,
      'recovery_point_objective' => 3600,
      'recovery_time_objective' => 7200,
      'failover' => 
      array (
        'enabled' => false,
        'automatic' => false,
        'secondary_site' => 
        array (
          'url' => NULL,
          'database_host' => NULL,
          'storage_path' => NULL,
        ),
        'health_check_interval' => 60,
        'failure_threshold' => 3,
      ),
      'replication' => 
      array (
        'enabled' => false,
        'method' => 'async',
        'interval' => 300,
      ),
    ),
    'verification' => 
    array (
      'enabled' => true,
      'methods' => 
      array (
        'checksum' => true,
        'size_check' => true,
        'compression_test' => true,
        'restore_test' => false,
      ),
      'checksum_algorithm' => 'sha256',
    ),
    'cleanup' => 
    array (
      'enabled' => true,
      'schedule' => 'daily',
      'strategy' => 'grandfather-father-son',
      'max_storage_size' => 50,
      'min_free_space' => 10,
    ),
    'encryption' => 
    array (
      'enabled' => false,
      'algorithm' => 'AES-256-CBC',
      'key' => NULL,
      'key_rotation' => 90,
    ),
    'monitoring' => 
    array (
      'enabled' => true,
      'metrics' => 
      array (
        'backup_size' => true,
        'backup_duration' => true,
        'success_rate' => true,
        'storage_usage' => true,
      ),
      'reports' => 
      array (
        'daily_summary' => true,
        'weekly_summary' => true,
        'monthly_summary' => true,
      ),
      'report_recipients' => 
      array (
        0 => 'admin@renthub.com',
      ),
    ),
  ),
  'broadcasting' => 
  array (
    'default' => 'reverb',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => 'renthub-key',
        'secret' => 'renthub-secret',
        'app_id' => 'renthub-local',
        'options' => 
        array (
          'host' => 'localhost',
          'port' => '8080',
          'scheme' => 'http',
          'useTLS' => false,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '',
        'secret' => '',
        'app_id' => '',
        'options' => 
        array (
          'cluster' => 'mt1',
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
    ),
    'reverb' => 
    array (
      'driver' => 'reverb',
      'key' => 'renthub-key',
      'secret' => 'renthub-secret',
      'app_id' => 'renthub-local',
      'options' => 
      array (
        'host' => 'localhost',
        'port' => '8080',
        'scheme' => 'http',
        'useTLS' => false,
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\framework/cache/data',
        'lock_path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
    ),
    'prefix' => '',
  ),
  'cache-strategy' => 
  array (
    'strategies' => 
    array (
      'api_responses' => 
      array (
        'enabled' => true,
        'ttl' => 3600,
        'driver' => 'redis',
        'tags' => 
        array (
          0 => 'api',
          1 => 'responses',
        ),
      ),
      'database_queries' => 
      array (
        'enabled' => true,
        'ttl' => 600,
        'driver' => 'redis',
        'tags' => 
        array (
          0 => 'database',
          1 => 'queries',
        ),
      ),
      'page_cache' => 
      array (
        'enabled' => true,
        'ttl' => 1800,
        'driver' => 'redis',
        'tags' => 
        array (
          0 => 'pages',
        ),
      ),
      'fragment_cache' => 
      array (
        'enabled' => true,
        'ttl' => 900,
        'driver' => 'redis',
        'tags' => 
        array (
          0 => 'fragments',
        ),
      ),
      'cdn_cache' => 
      array (
        'enabled' => true,
        'ttl' => 86400,
        'paths' => 
        array (
          0 => '/assets/*',
          1 => '/images/*',
          2 => '/js/*',
          3 => '/css/*',
        ),
      ),
    ),
    'invalidation' => 
    array (
      'models' => 
      array (
        'Property' => 
        array (
          0 => 'properties',
          1 => 'api',
          2 => 'pages',
        ),
        'Booking' => 
        array (
          0 => 'bookings',
          1 => 'api',
          2 => 'calendar',
        ),
        'User' => 
        array (
          0 => 'users',
          1 => 'api',
        ),
        'Review' => 
        array (
          0 => 'reviews',
          1 => 'api',
          2 => 'ratings',
        ),
      ),
      'events' => 
      array (
        'property.updated' => 
        array (
          0 => 'properties',
          1 => 'search',
        ),
        'booking.created' => 
        array (
          0 => 'bookings',
          1 => 'calendar',
          2 => 'availability',
        ),
        'review.created' => 
        array (
          0 => 'reviews',
          1 => 'ratings',
          2 => 'properties',
        ),
        'user.updated' => 
        array (
          0 => 'users',
          1 => 'profile',
        ),
      ),
    ),
    'warming' => 
    array (
      'enabled' => true,
      'schedule' => '0 */6 * * *',
      'routes' => 
      array (
        0 => '/api/properties/featured',
        1 => '/api/properties/popular',
        2 => '/api/reviews/recent',
        3 => '/api/locations/popular',
      ),
    ),
    'compression' => 
    array (
      'enabled' => true,
      'algorithm' => 'gzip',
      'level' => 6,
      'min_size' => 1024,
      'mime_types' => 
      array (
        0 => 'application/json',
        1 => 'application/xml',
        2 => 'text/html',
        3 => 'text/css',
        4 => 'text/javascript',
        5 => 'application/javascript',
      ),
    ),
    'browser_cache' => 
    array (
      'static_assets' => 
      array (
        'max_age' => 31536000,
        'paths' => 
        array (
          0 => '/assets/*',
          1 => '/images/*',
        ),
      ),
      'dynamic_content' => 
      array (
        'max_age' => 3600,
        'paths' => 
        array (
          0 => '/api/*',
        ),
      ),
      'no_cache' => 
      array (
        'paths' => 
        array (
          0 => '/admin/*',
          1 => '/auth/*',
        ),
      ),
    ),
    'query_optimization' => 
    array (
      'eager_loading' => 
      array (
        'enabled' => true,
        'relations' => 
        array (
          'Property' => 
          array (
            0 => 'owner',
            1 => 'amenities',
            2 => 'images',
            3 => 'location',
          ),
          'Booking' => 
          array (
            0 => 'property',
            1 => 'user',
            2 => 'payments',
          ),
          'Review' => 
          array (
            0 => 'user',
            1 => 'property',
            2 => 'booking',
          ),
        ),
      ),
      'chunk_size' => 1000,
      'pagination_default' => 20,
      'pagination_max' => 100,
    ),
    'connection_pooling' => 
    array (
      'database' => 
      array (
        'min_connections' => 5,
        'max_connections' => 20,
        'idle_timeout' => 300,
      ),
      'redis' => 
      array (
        'min_connections' => 2,
        'max_connections' => 10,
        'idle_timeout' => 300,
      ),
    ),
    'read_replicas' => 
    array (
      'enabled' => false,
      'connections' => 
      array (
        0 => 
        array (
          'host' => NULL,
          'weight' => 50,
        ),
        1 => 
        array (
          'host' => NULL,
          'weight' => 50,
        ),
      ),
      'strategy' => 'round-robin',
    ),
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => 'http://localhost:3000',
      1 => 'http://127.0.0.1:3000',
      2 => 'http://localhost:3001',
      3 => 'https://rent-ljgrpeajm-madsens-projects.vercel.app',
      4 => 'https://rent-19xinb37g-madsens-projects.vercel.app',
      5 => 'https://rent-hub-six.vercel.app',
      6 => 'https://rent-hub-beta.vercel.app',
    ),
    'allowed_origins_patterns' => 
    array (
      0 => '#^https?://([\\w-]+\\.)?renthub\\.com$#i',
      1 => '#^https?://[\\w-]+\\.vercel\\.app$#i',
      2 => '#^https?://[\\w-]+\\.on-forge\\.com$#i',
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
      0 => 'Authorization',
      1 => 'Content-Type',
      2 => 'X-Requested-With',
    ),
    'max_age' => 3600,
    'supports_credentials' => true,
  ),
  'database' => 
  array (
    'default' => 'sqlite',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'prefix' => '',
        'foreign_key_constraints' => true,
        'busy_timeout' => NULL,
        'journal_mode' => NULL,
        'synchronous' => NULL,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
          12 => true,
          2 => 30,
          1002 => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
          1000 => true,
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '5432',
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => 'localhost',
        'port' => '1433',
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
      'testing' => 
      array (
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'predis',
      'options' => 
      array (
        'cluster' => 'redis',
        'prefix' => 'renthub_database_',
      ),
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '0',
      ),
      'cache' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => '6379',
        'database' => '1',
      ),
    ),
  ),
  'database_pool' => 
  array (
    'pool' => 
    array (
      'max_connections' => 20,
      'min_connections' => 5,
      'connection_timeout' => 30,
      'idle_timeout' => 300,
      'health_check_enabled' => true,
      'health_check_interval' => 60,
      'retry' => 
      array (
        'max_attempts' => 3,
        'delay_ms' => 100,
        'backoff_multiplier' => 2,
        'max_delay_ms' => 1000,
      ),
      'slow_query' => 
      array (
        'enabled' => true,
        'threshold_seconds' => 1.0,
        'log_bindings' => false,
      ),
      'supported_drivers' => 
      array (
        'mysql' => true,
        'mariadb' => true,
        'pgsql' => true,
        'sqlite' => false,
      ),
      'monitoring' => 
      array (
        'enabled' => true,
        'metrics_retention_hours' => 24,
        'log_stats_interval' => 300,
      ),
    ),
  ),
  'dompdf' => 
  array (
    'show_warnings' => false,
    'public_path' => NULL,
    'convert_entities' => true,
    'options' => 
    array (
      'font_dir' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\fonts',
      'font_cache' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\fonts',
      'temp_dir' => 'C:\\Users\\aneme\\AppData\\Local\\Temp',
      'chroot' => 'C:\\laragon\\www\\RentHub\\backend',
      'allowed_protocols' => 
      array (
        'data://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'file://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'http://' => 
        array (
          'rules' => 
          array (
          ),
        ),
        'https://' => 
        array (
          'rules' => 
          array (
          ),
        ),
      ),
      'artifactPathValidation' => NULL,
      'log_output_file' => NULL,
      'enable_font_subsetting' => false,
      'pdf_backend' => 'CPDF',
      'default_media_type' => 'screen',
      'default_paper_size' => 'a4',
      'default_paper_orientation' => 'portrait',
      'default_font' => 'serif',
      'dpi' => 96,
      'enable_php' => false,
      'enable_javascript' => true,
      'enable_remote' => false,
      'allowed_remote_hosts' => NULL,
      'font_height_ratio' => 1.1,
      'enable_html5_parser' => true,
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\app/private',
        'serve' => true,
        'throw' => false,
        'report' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\app/public',
        'url' => 'http://localhost:8000/storage',
        'visibility' => 'public',
        'throw' => false,
        'report' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => '',
        'secret' => '',
        'region' => 'us-east-1',
        'bucket' => '',
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
        'report' => false,
      ),
    ),
    'links' => 
    array (
      'C:\\laragon\\www\\RentHub\\backend\\public\\storage' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\app/public',
    ),
  ),
  'gdpr' => 
  array (
    'data_retention_days' => 365,
    'anonymization' => 
    array (
      'keep_bookings' => true,
      'keep_reviews' => true,
      'keep_properties' => false,
      'anonymize_reviews' => true,
    ),
    'export' => 
    array (
      'format' => 'json',
      'include_files' => true,
      'max_file_size' => 100,
    ),
    'consent' => 
    array (
      'required_for_registration' => true,
      'cookie_consent_required' => true,
      'marketing_consent_required' => false,
    ),
    'pii_fields' => 
    array (
      'users' => 
      array (
        0 => 'name',
        1 => 'email',
        2 => 'phone',
        3 => 'address',
        4 => 'date_of_birth',
        5 => 'ssn',
      ),
      'bookings' => 
      array (
        0 => 'guest_name',
        1 => 'guest_email',
        2 => 'guest_phone',
      ),
      'reviews' => 
      array (
        0 => 'user_name',
      ),
    ),
    'encrypted_fields' => 
    array (
      'users' => 
      array (
        0 => 'ssn',
        1 => 'payment_info',
      ),
      'bookings' => 
      array (
        0 => 'guest_phone',
      ),
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => NULL,
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'formatter' => NULL,
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'mail' => 
  array (
    'default' => 'log',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '2525',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => 'localhost',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'RentHub',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\laragon\\www\\RentHub\\backend\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'monitoring' => 
  array (
    'provider' => 'datadog',
    'enabled' => true,
    'newrelic' => 
    array (
      'enabled' => false,
      'license_key' => NULL,
      'app_name' => 'RentHub',
      'transaction_tracer' => 
      array (
        'enabled' => true,
        'threshold' => 'apdex_f',
        'record_sql' => 'obfuscated',
      ),
      'error_collector' => 
      array (
        'enabled' => true,
        'ignore_status_codes' => 
        array (
          0 => 401,
          1 => 404,
        ),
      ),
      'browser_monitoring' => 
      array (
        'enabled' => true,
        'auto_instrument' => true,
      ),
    ),
    'datadog' => 
    array (
      'enabled' => false,
      'api_key' => NULL,
      'app_key' => NULL,
      'host' => 'api.datadoghq.com',
      'apm' => 
      array (
        'enabled' => true,
        'service_name' => 'renthub',
        'env' => 'production',
        'version' => '1.0.0',
      ),
      'metrics' => 
      array (
        'enabled' => true,
        'namespace' => 'renthub',
        'flush_interval' => 10,
      ),
      'logs' => 
      array (
        'enabled' => true,
        'source' => 'laravel',
        'service' => 'renthub-api',
      ),
      'trace' => 
      array (
        'enabled' => true,
        'sample_rate' => 1.0,
      ),
    ),
    'prometheus' => 
    array (
      'enabled' => false,
      'namespace' => 'renthub',
      'metrics_route' => '/metrics',
      'collectors' => 
      array (
        'default' => true,
        'requests' => true,
        'database' => true,
        'cache' => true,
        'queue' => true,
      ),
      'buckets' => 
      array (
        'request_duration' => 
        array (
          0 => 0.005,
          1 => 0.01,
          2 => 0.025,
          3 => 0.05,
          4 => 0.075,
          5 => 0.1,
          6 => 0.25,
          7 => 0.5,
          8 => 0.75,
          9 => 1.0,
          10 => 2.5,
          11 => 5.0,
          12 => 7.5,
          13 => 10.0,
        ),
      ),
    ),
    'sentry' => 
    array (
      'enabled' => false,
      'dsn' => '',
      'environment' => 'local',
      'release' => NULL,
      'traces_sample_rate' => 0.1,
      'profiles_sample_rate' => 0.1,
      'send_default_pii' => false,
      'attach_stacktrace' => true,
      'before_send' => NULL,
      'breadcrumbs' => 
      array (
        'sql_queries' => true,
        'sql_bindings' => true,
        'queue_info' => true,
        'command_info' => true,
      ),
      'integrations' => 
      array (
        'laravel' => true,
        'query' => true,
        'redis' => true,
      ),
    ),
    'metrics' => 
    array (
      'enabled' => true,
      'collect' => 
      array (
        'requests' => true,
        'exceptions' => true,
        'database_queries' => true,
        'cache_operations' => true,
        'queue_jobs' => true,
        'external_api_calls' => true,
      ),
      'custom_metrics' => 
      array (
        'properties_viewed' => true,
        'bookings_created' => true,
        'payments_processed' => true,
        'searches_performed' => true,
      ),
    ),
    'log_aggregation' => 
    array (
      'enabled' => false,
      'provider' => 'elk',
      'elk' => 
      array (
        'elasticsearch' => 
        array (
          'hosts' => 'localhost:9200',
          'index_prefix' => 'renthub',
          'index_pattern' => 'daily',
        ),
        'logstash' => 
        array (
          'host' => 'localhost',
          'port' => 5044,
        ),
      ),
      'splunk' => 
      array (
        'url' => NULL,
        'token' => NULL,
        'index' => 'renthub',
      ),
      'cloudwatch' => 
      array (
        'group' => 'renthub',
        'stream' => 'application',
        'region' => 'us-east-1',
      ),
    ),
    'uptime' => 
    array (
      'enabled' => true,
      'providers' => 
      array (
        'pingdom' => 
        array (
          'enabled' => false,
          'api_key' => NULL,
          'check_ids' => '',
        ),
        'uptimerobot' => 
        array (
          'enabled' => false,
          'api_key' => NULL,
          'monitor_ids' => '',
        ),
        'statuspage' => 
        array (
          'enabled' => false,
          'page_id' => NULL,
          'api_key' => NULL,
        ),
      ),
      'checks' => 
      array (
        'http' => 
        array (
          'enabled' => true,
          'urls' => 
          array (
            0 => 'http://localhost:8000',
            1 => 'http://localhost:8000/api/health',
          ),
          'interval' => 60,
          'timeout' => 10,
        ),
        'ssl' => 
        array (
          'enabled' => true,
          'days_before_expiry_alert' => 30,
        ),
      ),
    ),
    'performance' => 
    array (
      'enabled' => true,
      'thresholds' => 
      array (
        'slow_query' => 1000,
        'slow_request' => 2000,
        'high_memory' => 512,
      ),
      'profiling' => 
      array (
        'enabled' => false,
        'sample_rate' => 0.01,
        'storage' => 'redis',
      ),
    ),
    'alerts' => 
    array (
      'enabled' => true,
      'channels' => 
      array (
        'slack' => 
        array (
          'enabled' => false,
          'webhook_url' => NULL,
          'channel' => '#alerts',
        ),
        'email' => 
        array (
          'enabled' => true,
          'recipients' => 
          array (
            0 => '',
          ),
        ),
        'pagerduty' => 
        array (
          'enabled' => false,
          'integration_key' => NULL,
        ),
        'opsgenie' => 
        array (
          'enabled' => false,
          'api_key' => NULL,
        ),
      ),
      'rules' => 
      array (
        'high_error_rate' => 
        array (
          'threshold' => 5,
          'window' => 300,
          'severity' => 'critical',
        ),
        'slow_responses' => 
        array (
          'threshold' => 3000,
          'count' => 10,
          'window' => 300,
          'severity' => 'warning',
        ),
        'high_cpu' => 
        array (
          'threshold' => 80,
          'duration' => 600,
          'severity' => 'warning',
        ),
        'high_memory' => 
        array (
          'threshold' => 85,
          'duration' => 600,
          'severity' => 'critical',
        ),
        'database_connection_errors' => 
        array (
          'threshold' => 3,
          'window' => 60,
          'severity' => 'critical',
        ),
      ),
    ),
  ),
  'performance' => 
  array (
    'cache' => 
    array (
      'enabled' => true,
      'default_ttl' => 3600,
      'query_cache_ttl' => 600,
      'page_cache_ttl' => 1800,
      'fragment_cache_ttl' => 900,
    ),
    'compression' => 
    array (
      'enabled' => true,
      'brotli' => true,
      'gzip' => true,
      'level' => 6,
    ),
    'query_optimization' => 
    array (
      'enabled' => true,
      'eager_loading' => true,
      'chunk_size' => 1000,
      'log_slow_queries' => true,
      'slow_query_threshold' => 100,
    ),
    'database' => 
    array (
      'connection_pool' => true,
      'max_connections' => 100,
      'read_write_split' => false,
    ),
    'cdn' => 
    array (
      'enabled' => false,
      'url' => NULL,
      'assets_path' => '/assets',
    ),
    'browser_cache' => 
    array (
      'enabled' => true,
      'max_age' => 86400,
      'static_assets_max_age' => 31536000,
    ),
  ),
  'permission' => 
  array (
    'models' => 
    array (
      'permission' => 'Spatie\\Permission\\Models\\Permission',
      'role' => 'Spatie\\Permission\\Models\\Role',
    ),
    'table_names' => 
    array (
      'roles' => 'roles',
      'permissions' => 'permissions',
      'model_has_permissions' => 'model_has_permissions',
      'model_has_roles' => 'model_has_roles',
      'role_has_permissions' => 'role_has_permissions',
    ),
    'column_names' => 
    array (
      'role_pivot_key' => NULL,
      'permission_pivot_key' => NULL,
      'model_morph_key' => 'model_id',
      'team_foreign_key' => 'team_id',
    ),
    'register_permission_check_method' => true,
    'register_octane_reset_listener' => false,
    'events_enabled' => false,
    'teams' => false,
    'team_resolver' => 'Spatie\\Permission\\DefaultTeamResolver',
    'use_passport_client_credentials' => false,
    'display_permission_in_exception' => false,
    'display_role_in_exception' => false,
    'enable_wildcard_permission' => false,
    'cache' => 
    array (
      'expiration_time' => 
      \DateInterval::__set_state(array(
         'from_string' => true,
         'date_string' => '24 hours',
      )),
      'key' => 'spatie.permission.cache',
      'store' => 'default',
    ),
  ),
  'production' => 
  array (
    'debug' => false,
    'url' => 'http://localhost:8000',
    'asset_url' => NULL,
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => 'base64:fodQaKMrekfeE/3vj/TdJm9+4mslWFRMLN6x9LBB5U4=',
    'cipher' => 'AES-256-CBC',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'App\\Providers\\AppServiceProvider',
      23 => 'App\\Providers\\AuthServiceProvider',
      24 => 'App\\Providers\\EventServiceProvider',
      25 => 'App\\Providers\\RouteServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
    ),
  ),
  'queue' => 
  array (
    'default' => 'sync',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => '',
        'secret' => '',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
    ),
    'batching' => 
    array (
      'database' => 'sqlite',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'sqlite',
      'table' => 'failed_jobs',
    ),
  ),
  'referral' => 
  array (
    'referrer_points' => 500,
    'referrer_amount' => 0,
    'referred_points' => 100,
    'referred_amount' => 10.0,
    'code_length' => 8,
    'code_prefix' => '',
    'code_expiry_days' => 30,
    'min_booking_amount' => 50,
    'require_booking_completion' => true,
    'max_referrals_per_user' => NULL,
    'max_redemptions_per_code' => NULL,
    'enable_referral_discount' => true,
    'enable_referral_points' => true,
    'enable_leaderboard' => true,
  ),
  'reverb' => 
  array (
    'default' => 'reverb',
    'servers' => 
    array (
      'reverb' => 
      array (
        'host' => '0.0.0.0',
        'port' => '8080',
        'path' => '',
        'hostname' => 'localhost',
        'options' => 
        array (
          'tls' => 
          array (
          ),
        ),
        'max_request_size' => 10000,
        'scaling' => 
        array (
          'enabled' => false,
          'channel' => 'reverb',
          'server' => 
          array (
            'url' => NULL,
            'host' => '127.0.0.1',
            'port' => '6379',
            'username' => NULL,
            'password' => NULL,
            'database' => '0',
            'timeout' => 60,
          ),
        ),
        'pulse_ingest_interval' => 15,
        'telescope_ingest_interval' => 15,
      ),
    ),
    'apps' => 
    array (
      'provider' => 'config',
      'apps' => 
      array (
        0 => 
        array (
          'key' => 'renthub-key',
          'secret' => 'renthub-secret',
          'app_id' => 'renthub-local',
          'options' => 
          array (
            'host' => 'localhost',
            'port' => '8080',
            'scheme' => 'http',
            'useTLS' => false,
          ),
          'allowed_origins' => 
          array (
            0 => '*',
          ),
          'ping_interval' => 60,
          'activity_timeout' => 30,
          'max_connections' => NULL,
          'max_message_size' => 10000,
        ),
      ),
    ),
  ),
  'sanctum' => 
  array (
    'stateful' => 
    array (
      0 => 'localhost:3000',
      1 => 'localhost',
      2 => '127.0.0.1:3000',
    ),
    'guard' => 
    array (
      0 => 'web',
    ),
    'expiration' => NULL,
    'token_prefix' => '',
    'middleware' => 
    array (
      'authenticate_session' => 'Laravel\\Sanctum\\Http\\Middleware\\AuthenticateSession',
      'encrypt_cookies' => 'Illuminate\\Cookie\\Middleware\\EncryptCookies',
    ),
  ),
  'scaling' => 
  array (
    'load_balancer' => 
    array (
      'enabled' => false,
      'algorithm' => 'round_robin',
      'health_check_interval' => 30,
      'nodes' => 
      array (
        0 => 
        array (
          'host' => '10.0.1.10',
          'port' => 80,
          'weight' => 1,
        ),
        1 => 
        array (
          'host' => '10.0.1.11',
          'port' => 80,
          'weight' => 1,
        ),
      ),
    ),
    'auto_scaling' => 
    array (
      'enabled' => false,
      'provider' => 'aws',
      'min_instances' => 2,
      'max_instances' => 10,
      'metrics' => 
      array (
        'cpu' => 
        array (
          'target' => 70,
          'scale_up_threshold' => 80,
          'scale_down_threshold' => 30,
        ),
        'memory' => 
        array (
          'target' => 75,
          'scale_up_threshold' => 85,
          'scale_down_threshold' => 40,
        ),
        'requests' => 
        array (
          'target' => 1000,
          'scale_up_threshold' => 1500,
          'scale_down_threshold' => 500,
        ),
      ),
      'cooldown' => 
      array (
        'scale_up' => 300,
        'scale_down' => 600,
      ),
    ),
    'database_replication' => 
    array (
      'enabled' => false,
      'read_replicas' => 
      array (
        0 => 
        array (
          'host' => '127.0.0.1',
          'port' => 3306,
          'database' => 'renthub',
          'username' => 'root',
          'password' => '',
          'weight' => 1,
        ),
        1 => 
        array (
          'host' => '127.0.0.1',
          'port' => 3306,
          'database' => 'renthub',
          'username' => 'root',
          'password' => '',
          'weight' => 1,
        ),
      ),
      'write_master' => 
      array (
        'host' => '127.0.0.1',
        'port' => 3306,
        'database' => 'C:\\\\laragon\\\\www\\\\RentHub\\\\backend\\\\database\\\\database.sqlite',
        'username' => 'root',
        'password' => '',
      ),
      'sticky_sessions' => true,
    ),
    'microservices' => 
    array (
      'enabled' => false,
      'gateway_url' => 'http://localhost:8080',
      'services' => 
      array (
        'auth' => 
        array (
          'url' => 'http://auth-service:8001',
          'timeout' => 5,
          'retry' => 3,
        ),
        'properties' => 
        array (
          'url' => 'http://properties-service:8002',
          'timeout' => 10,
          'retry' => 2,
        ),
        'bookings' => 
        array (
          'url' => 'http://bookings-service:8003',
          'timeout' => 8,
          'retry' => 3,
        ),
        'payments' => 
        array (
          'url' => 'http://payments-service:8004',
          'timeout' => 15,
          'retry' => 3,
        ),
        'notifications' => 
        array (
          'url' => 'http://notifications-service:8005',
          'timeout' => 5,
          'retry' => 2,
        ),
      ),
      'circuit_breaker' => 
      array (
        'enabled' => true,
        'failure_threshold' => 5,
        'timeout' => 60,
        'retry_timeout' => 30,
      ),
    ),
    'cache_scaling' => 
    array (
      'strategy' => 'redis_cluster',
      'redis_cluster' => 
      array (
        'nodes' => 
        array (
          0 => '127.0.0.1:6379',
          1 => '127.0.0.1:6380',
          2 => '127.0.0.1:6381',
        ),
        'options' => 
        array (
          'cluster' => 'redis',
          'prefix' => 'renthub_cache:',
        ),
      ),
    ),
    'queue_scaling' => 
    array (
      'enabled' => false,
      'driver' => 'redis',
      'workers' => 
      array (
        'min' => 1,
        'max' => 10,
        'auto_scale' => true,
        'scale_up_threshold' => 100,
        'scale_down_threshold' => 10,
      ),
      'supervisor' => 
      array (
        'enabled' => true,
        'command' => 'php artisan queue:work',
        'processes' => 3,
        'max_time' => 3600,
        'max_jobs' => 1000,
        'memory_limit' => 512,
      ),
    ),
    'health_check' => 
    array (
      'enabled' => true,
      'endpoint' => '/health',
      'checks' => 
      array (
        'database' => true,
        'redis' => true,
        'queue' => true,
        'storage' => true,
        'external_services' => true,
      ),
      'thresholds' => 
      array (
        'response_time' => 1000,
        'memory_usage' => 90,
        'disk_usage' => 85,
      ),
    ),
  ),
  'scout' => 
  array (
    'driver' => 'meilisearch',
    'prefix' => '',
    'queue' => true,
    'after_commit' => false,
    'chunk' => 
    array (
      'searchable' => 500,
      'unsearchable' => 500,
    ),
    'soft_delete' => false,
    'identify' => false,
    'algolia' => 
    array (
      'id' => '',
      'secret' => '',
    ),
    'meilisearch' => 
    array (
      'host' => 'http://127.0.0.1:7700',
      'key' => '',
      'index-settings' => 
      array (
        'properties' => 
        array (
          'filterableAttributes' => 
          array (
            0 => 'property_type',
            1 => 'city',
            2 => 'country',
            3 => 'price',
            4 => 'bedrooms',
            5 => 'bathrooms',
            6 => 'guests',
            7 => 'rating',
            8 => 'status',
            9 => 'is_instant_book',
          ),
          'sortableAttributes' => 
          array (
            0 => 'price',
            1 => 'rating',
            2 => 'created_at',
          ),
          'searchableAttributes' => 
          array (
            0 => 'title',
            1 => 'description',
            2 => 'city',
            3 => 'country',
            4 => 'address',
          ),
          'displayedAttributes' => 
          array (
            0 => '*',
          ),
        ),
      ),
    ),
    'typesense' => 
    array (
      'client-settings' => 
      array (
        'api_key' => 'xyz',
        'nodes' => 
        array (
          0 => 
          array (
            'host' => 'localhost',
            'port' => '8108',
            'path' => '',
            'protocol' => 'http',
          ),
        ),
        'nearest_node' => 
        array (
          'host' => 'localhost',
          'port' => '8108',
          'path' => '',
          'protocol' => 'http',
        ),
        'connection_timeout_seconds' => 2,
        'healthcheck_interval_seconds' => 30,
        'num_retries' => 3,
        'retry_interval_seconds' => 1,
      ),
      'model-settings' => 
      array (
      ),
    ),
  ),
  'security' => 
  array (
    'encryption' => 
    array (
      'at_rest' => 
      array (
        'enabled' => true,
        'algorithm' => 'aes-256-gcm',
        'key_rotation_days' => 90,
      ),
      'in_transit' => 
      array (
        'force_tls' => true,
        'min_tls_version' => '1.3',
        'allowed_ciphers' => 
        array (
          0 => 'TLS_AES_256_GCM_SHA384',
          1 => 'TLS_AES_128_GCM_SHA256',
          2 => 'TLS_CHACHA20_POLY1305_SHA256',
        ),
      ),
    ),
    'data_protection' => 
    array (
      'pii_fields' => 
      array (
        0 => 'email',
        1 => 'phone',
        2 => 'ssn',
        3 => 'tax_id',
        4 => 'passport_number',
        5 => 'driving_license',
        6 => 'date_of_birth',
        7 => 'address',
        8 => 'bank_account',
        9 => 'credit_card',
      ),
      'anonymization' => 
      array (
        'enabled' => true,
        'method' => 'hash',
        'retention_days' => 30,
      ),
    ),
    'gdpr' => 
    array (
      'enabled' => true,
      'data_retention_days' => 2555,
      'deletion_grace_period_days' => 30,
      'export_format' => 'json',
      'consent_tracking' => true,
      'right_to_be_forgotten' => true,
      'data_portability' => true,
    ),
    'ccpa' => 
    array (
      'enabled' => true,
      'do_not_sell' => true,
      'opt_out_enabled' => true,
      'data_categories' => 
      array (
        0 => 'identifiers',
        1 => 'commercial_information',
        2 => 'internet_activity',
        3 => 'geolocation',
        4 => 'professional_information',
      ),
    ),
    'app_security' => 
    array (
      'sql_injection' => 
      array (
        'enabled' => true,
        'use_prepared_statements' => true,
        'validate_input' => true,
      ),
      'xss_protection' => 
      array (
        'enabled' => true,
        'sanitize_output' => true,
        'escape_html' => true,
        'content_security_policy' => true,
      ),
      'csrf_protection' => 
      array (
        'enabled' => true,
        'token_lifetime' => 7200,
        'per_page_token' => true,
      ),
    ),
    'rate_limiting' => 
    array (
      'enabled' => true,
      'driver' => 'redis',
      'defaults' => 
      array (
        'api' => 
        array (
          'max_attempts' => 60,
          'decay_minutes' => 1,
        ),
        'auth' => 
        array (
          'max_attempts' => 5,
          'decay_minutes' => 15,
        ),
        'uploads' => 
        array (
          'max_attempts' => 10,
          'decay_minutes' => 60,
        ),
      ),
      'per_user' => 
      array (
        'guest' => 
        array (
          'max' => 60,
          'decay' => 1,
        ),
        'tenant' => 
        array (
          'max' => 120,
          'decay' => 1,
        ),
        'landlord' => 
        array (
          'max' => 300,
          'decay' => 1,
        ),
        'admin' => 
        array (
          'max' => 1000,
          'decay' => 1,
        ),
      ),
    ),
    'ddos_protection' => 
    array (
      'enabled' => true,
      'max_requests_per_second' => 10,
      'ban_duration_minutes' => 60,
      'whitelist_ips' => 
      array (
        0 => '',
      ),
      'blacklist_ips' => 
      array (
        0 => '',
      ),
      'challenge_suspicious_traffic' => true,
    ),
    'headers' => 
    array (
      'X-Content-Type-Options' => 'nosniff',
      'X-Frame-Options' => 'DENY',
      'X-XSS-Protection' => '1; mode=block',
      'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
      'Referrer-Policy' => 'strict-origin-when-cross-origin',
      'Permissions-Policy' => 'geolocation=(self), microphone=(), camera=()',
      'Content-Security-Policy' => 'default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'; style-src \'self\' \'unsafe-inline\'; img-src \'self\' data: https:; font-src \'self\' data:; connect-src \'self\'; frame-ancestors \'none\'; base-uri \'self\'; form-action \'self\'',
    ),
    'input_validation' => 
    array (
      'enabled' => true,
      'sanitize_strings' => true,
      'strip_tags' => true,
      'max_input_length' => 10000,
      'allowed_file_types' => 
      array (
        'images' => 
        array (
          0 => 'jpg',
          1 => 'jpeg',
          2 => 'png',
          3 => 'gif',
          4 => 'webp',
        ),
        'documents' => 
        array (
          0 => 'pdf',
          1 => 'doc',
          2 => 'docx',
          3 => 'xls',
          4 => 'xlsx',
        ),
        'archives' => 
        array (
          0 => 'zip',
        ),
      ),
      'max_file_size' => 10485760,
    ),
    'file_upload' => 
    array (
      'scan_for_viruses' => true,
      'validate_mime_type' => true,
      'randomize_filenames' => true,
      'store_outside_webroot' => true,
      'max_size' => 10485760,
      'allowed_extensions' => 
      array (
        0 => 'jpg',
        1 => 'jpeg',
        2 => 'png',
        3 => 'gif',
        4 => 'pdf',
        5 => 'doc',
        6 => 'docx',
      ),
      'forbidden_extensions' => 
      array (
        0 => 'php',
        1 => 'exe',
        2 => 'sh',
        3 => 'bat',
        4 => 'cmd',
        5 => 'com',
      ),
    ),
    'session' => 
    array (
      'secure' => true,
      'http_only' => true,
      'same_site' => 'lax',
      'lifetime' => 120,
      'idle_timeout' => 30,
      'regenerate_on_login' => true,
      'device_fingerprinting' => true,
    ),
    'api' => 
    array (
      'versioning' => 
      array (
        'enabled' => true,
        'header' => 'Accept',
        'deprecation_notice_versions' => 2,
      ),
      'authentication' => 
      array (
        'required' => true,
        'methods' => 
        array (
          0 => 'jwt',
          1 => 'api_key',
          2 => 'oauth',
        ),
      ),
      'authorization' => 
      array (
        'rbac_enabled' => true,
        'check_permissions' => true,
      ),
      'validation' => 
      array (
        'strict_mode' => true,
        'fail_on_unknown_fields' => false,
      ),
    ),
    'audit' => 
    array (
      'enabled' => true,
      'log_authentication' => true,
      'log_authorization_failures' => true,
      'log_data_access' => true,
      'log_data_modifications' => true,
      'log_admin_actions' => true,
      'retention_days' => 365,
    ),
    'monitoring' => 
    array (
      'enabled' => true,
      'alert_on_suspicious_activity' => true,
      'alert_channels' => 
      array (
        0 => 'email',
        1 => 'slack',
        2 => 'sms',
      ),
      'thresholds' => 
      array (
        'failed_logins' => 5,
        'rate_limit_violations' => 10,
        'unauthorized_access_attempts' => 3,
      ),
    ),
    'password' => 
    array (
      'min_length' => 8,
      'require_uppercase' => true,
      'require_lowercase' => true,
      'require_numbers' => true,
      'require_symbols' => true,
      'check_compromised' => true,
      'expiry_days' => 90,
      'prevent_reuse_count' => 5,
    ),
    'two_factor' => 
    array (
      'enabled' => false,
      'enforced_for_roles' => 
      array (
        0 => 'admin',
      ),
      'methods' => 
      array (
        0 => 'totp',
        1 => 'sms',
        2 => 'email',
      ),
      'backup_codes_count' => 10,
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'ses' => 
    array (
      'key' => '',
      'secret' => '',
      'region' => 'us-east-1',
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'google' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => 'http://localhost:8000/api/v1/auth/google/callback',
      'calendar_client_id' => '',
      'calendar_client_secret' => '',
      'calendar_redirect_uri' => 'http://localhost:8000/api/v1/google-calendar/callback',
    ),
    'facebook' => 
    array (
      'client_id' => '',
      'client_secret' => '',
      'redirect' => 'http://localhost:8000/api/v1/auth/facebook/callback',
    ),
    'twilio' => 
    array (
      'sid' => '',
      'token' => '',
      'from' => '',
    ),
    'sendgrid' => 
    array (
      'api_key' => NULL,
      'from_email' => 'hello@example.com',
      'from_name' => 'RentHub',
    ),
    'stripe' => 
    array (
      'key' => '',
      'secret' => '',
      'webhook_secret' => '',
    ),
    'beams' => 
    array (
      'instance_id' => '0223b504-a3c5-40f5-a2d2-110c12c80fb4',
      'secret_key' => '836A91127B194EBDCC22FB8372A0C691BDEFBE04C12B453CF3238434713342D5',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'renthub_session',
    'path' => '/',
    'domain' => '',
    'secure' => true,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'websockets' => 
  array (
    'apps' => 
    array (
      0 => 
      array (
        'id' => '',
        'name' => 'RentHub',
        'key' => '',
        'secret' => '',
        'enable_client_messages' => false,
        'enable_statistics' => true,
      ),
    ),
    'host' => '0.0.0.0',
    'port' => 6001,
    'ssl' => 
    array (
      'local_cert' => NULL,
      'local_pk' => NULL,
      'passphrase' => NULL,
    ),
    'max_request_size_in_kb' => 250,
    'channel_limits' => 
    array (
      'presence' => 100,
      'private' => 100,
      'public' => 100,
    ),
    'statistics' => 
    array (
      'model' => 'BeyondCode\\LaravelWebSockets\\Statistics\\Models\\WebSocketsStatisticsEntry',
      'interval_in_seconds' => 60,
      'delete_statistics_older_than_days' => 60,
    ),
  ),
  'blade-heroicons' => 
  array (
    'prefix' => 'heroicon',
    'fallback' => '',
    'class' => '',
    'attributes' => 
    array (
    ),
  ),
  'blade-icons' => 
  array (
    'sets' => 
    array (
    ),
    'class' => '',
    'attributes' => 
    array (
    ),
    'fallback' => '',
    'components' => 
    array (
      'disabled' => false,
      'default' => 'icon',
    ),
  ),
  'filament' => 
  array (
    'broadcasting' => 
    array (
    ),
    'default_filesystem_disk' => 'local',
    'assets_path' => NULL,
    'cache_path' => 'C:\\laragon\\www\\RentHub\\backend\\bootstrap/cache/filament',
    'livewire_loading_delay' => 'default',
    'file_generation' => 
    array (
      'flags' => 
      array (
      ),
    ),
    'system_route_prefix' => 'filament',
  ),
  'livewire' => 
  array (
    'class_namespace' => 'App\\Livewire',
    'view_path' => 'C:\\laragon\\www\\RentHub\\backend\\resources\\views/livewire',
    'layout' => 'components.layouts.app',
    'lazy_placeholder' => NULL,
    'temporary_file_upload' => 
    array (
      'disk' => NULL,
      'rules' => NULL,
      'directory' => NULL,
      'middleware' => NULL,
      'preview_mimes' => 
      array (
        0 => 'png',
        1 => 'gif',
        2 => 'bmp',
        3 => 'svg',
        4 => 'wav',
        5 => 'mp4',
        6 => 'mov',
        7 => 'avi',
        8 => 'wmv',
        9 => 'mp3',
        10 => 'm4a',
        11 => 'jpg',
        12 => 'jpeg',
        13 => 'mpga',
        14 => 'webp',
        15 => 'wma',
      ),
      'max_upload_time' => 5,
      'cleanup' => true,
    ),
    'render_on_redirect' => false,
    'legacy_model_binding' => false,
    'inject_assets' => true,
    'navigate' => 
    array (
      'show_progress_bar' => true,
      'progress_bar_color' => '#2299dd',
    ),
    'inject_morph_markers' => true,
    'pagination_theme' => 'tailwind',
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'pre_calculate_formulas' => false,
      'strict_null_comparison' => false,
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
        'output_encoding' => '',
        'test_auto_detect' => true,
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
    ),
    'imports' => 
    array (
      'read_only' => true,
      'ignore_empty' => false,
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => NULL,
        'enclosure' => '"',
        'escape_character' => '\\',
        'contiguous' => false,
        'input_encoding' => 'guess',
      ),
      'properties' => 
      array (
        'creator' => '',
        'lastModifiedBy' => '',
        'title' => '',
        'description' => '',
        'subject' => '',
        'keywords' => '',
        'category' => '',
        'manager' => '',
        'company' => '',
      ),
      'cells' => 
      array (
        'middleware' => 
        array (
        ),
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
      'default_ttl' => 10800,
    ),
    'transactions' => 
    array (
      'handler' => 'db',
      'db' => 
      array (
        'connection' => NULL,
      ),
    ),
    'temporary_files' => 
    array (
      'local_path' => 'C:\\laragon\\www\\RentHub\\backend\\storage\\framework/cache/laravel-excel',
      'local_permissions' => 
      array (
      ),
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
