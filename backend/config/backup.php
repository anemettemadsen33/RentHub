<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automated backup and disaster recovery
    |
    */

    'enabled' => env('BACKUP_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Database Backups
    |--------------------------------------------------------------------------
    */

    'database' => [
        'enabled' => env('BACKUP_DATABASE_ENABLED', true),
        
        'connections' => [
            'mysql' => [
                'driver' => 'mysql',
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'renthub'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
            ],
        ],

        'backup_options' => [
            'compress' => true,
            'compression_type' => 'gzip', // gzip, bzip2
            'include_routines' => true,
            'include_triggers' => true,
            'add_drop_table' => true,
            'add_drop_trigger' => true,
            'single_transaction' => true,
            'lock_tables' => false,
            'quick' => true,
            'extended_insert' => true,
        ],

        'schedule' => [
            'full_backup' => env('BACKUP_DB_FULL_SCHEDULE', 'daily'), // hourly, daily, weekly
            'incremental_backup' => env('BACKUP_DB_INCREMENTAL_SCHEDULE', 'hourly'),
            'time' => env('BACKUP_DB_TIME', '02:00'), // HH:MM format
        ],

        'retention' => [
            'daily' => env('BACKUP_DB_RETENTION_DAILY', 7), // days
            'weekly' => env('BACKUP_DB_RETENTION_WEEKLY', 4), // weeks
            'monthly' => env('BACKUP_DB_RETENTION_MONTHLY', 3), // months
            'yearly' => env('BACKUP_DB_RETENTION_YEARLY', 1), // years
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | File Backups
    |--------------------------------------------------------------------------
    */

    'files' => [
        'enabled' => env('BACKUP_FILES_ENABLED', true),
        
        'include' => [
            'storage' => [
                'path' => storage_path('app'),
                'exclude' => [
                    'cache',
                    'logs',
                    'framework/cache',
                    'framework/sessions',
                    'framework/views',
                ],
            ],
            'public' => [
                'path' => public_path(),
                'exclude' => [
                    'build',
                    'hot',
                ],
            ],
            'uploads' => [
                'path' => storage_path('app/public'),
                'exclude' => [],
            ],
        ],

        'backup_options' => [
            'compress' => true,
            'compression_type' => 'tar.gz', // tar.gz, zip
            'incremental' => true,
            'follow_symlinks' => false,
            'max_file_size' => env('BACKUP_MAX_FILE_SIZE', 100), // MB
        ],

        'schedule' => [
            'full_backup' => env('BACKUP_FILES_FULL_SCHEDULE', 'daily'),
            'incremental_backup' => env('BACKUP_FILES_INCREMENTAL_SCHEDULE', 'hourly'),
            'time' => env('BACKUP_FILES_TIME', '03:00'),
        ],

        'retention' => [
            'daily' => env('BACKUP_FILES_RETENTION_DAILY', 7),
            'weekly' => env('BACKUP_FILES_RETENTION_WEEKLY', 4),
            'monthly' => env('BACKUP_FILES_RETENTION_MONTHLY', 3),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Storage Destinations
    |--------------------------------------------------------------------------
    */

    'destinations' => [
        'local' => [
            'enabled' => env('BACKUP_LOCAL_ENABLED', true),
            'path' => env('BACKUP_LOCAL_PATH', storage_path('backups')),
            'permissions' => 0755,
        ],

        's3' => [
            'enabled' => env('BACKUP_S3_ENABLED', false),
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('BACKUP_S3_BUCKET', 'renthub-backups'),
            'path' => env('BACKUP_S3_PATH', 'backups'),
            'storage_class' => env('BACKUP_S3_STORAGE_CLASS', 'STANDARD_IA'), // STANDARD, STANDARD_IA, GLACIER
        ],

        'ftp' => [
            'enabled' => env('BACKUP_FTP_ENABLED', false),
            'host' => env('BACKUP_FTP_HOST'),
            'port' => env('BACKUP_FTP_PORT', 21),
            'username' => env('BACKUP_FTP_USERNAME'),
            'password' => env('BACKUP_FTP_PASSWORD'),
            'root' => env('BACKUP_FTP_ROOT', '/backups'),
            'passive' => true,
            'ssl' => env('BACKUP_FTP_SSL', true),
        ],

        'dropbox' => [
            'enabled' => env('BACKUP_DROPBOX_ENABLED', false),
            'token' => env('DROPBOX_TOKEN'),
            'path' => env('BACKUP_DROPBOX_PATH', '/RentHub/backups'),
        ],

        'google_drive' => [
            'enabled' => env('BACKUP_GOOGLE_DRIVE_ENABLED', false),
            'client_id' => env('GOOGLE_DRIVE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_DRIVE_CLIENT_SECRET'),
            'refresh_token' => env('GOOGLE_DRIVE_REFRESH_TOKEN'),
            'folder_id' => env('BACKUP_GOOGLE_DRIVE_FOLDER'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Notifications
    |--------------------------------------------------------------------------
    */

    'notifications' => [
        'enabled' => env('BACKUP_NOTIFICATIONS_ENABLED', true),
        
        'events' => [
            'backup_success' => true,
            'backup_failure' => true,
            'cleanup_success' => true,
            'cleanup_failure' => true,
            'restore_success' => true,
            'restore_failure' => true,
        ],

        'channels' => [
            'mail' => [
                'enabled' => env('BACKUP_NOTIFY_EMAIL', true),
                'to' => explode(',', env('BACKUP_EMAIL_RECIPIENTS', 'admin@renthub.com')),
            ],
            'slack' => [
                'enabled' => env('BACKUP_NOTIFY_SLACK', false),
                'webhook_url' => env('BACKUP_SLACK_WEBHOOK'),
                'channel' => env('BACKUP_SLACK_CHANNEL', '#backups'),
            ],
            'discord' => [
                'enabled' => env('BACKUP_NOTIFY_DISCORD', false),
                'webhook_url' => env('BACKUP_DISCORD_WEBHOOK'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Testing
    |--------------------------------------------------------------------------
    */

    'testing' => [
        'enabled' => env('BACKUP_TESTING_ENABLED', true),
        
        'schedule' => env('BACKUP_TESTING_SCHEDULE', 'weekly'), // daily, weekly, monthly
        
        'tests' => [
            'database_restore' => true,
            'file_integrity' => true,
            'backup_size_check' => true,
            'backup_age_check' => true,
        ],

        'test_database' => [
            'connection' => 'mysql_test',
            'database' => env('BACKUP_TEST_DATABASE', 'renthub_backup_test'),
        ],

        'thresholds' => [
            'max_restore_time' => 300, // seconds
            'max_backup_age' => 86400, // seconds (24 hours)
            'min_backup_size' => 1024, // KB
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Disaster Recovery
    |--------------------------------------------------------------------------
    */

    'disaster_recovery' => [
        'enabled' => env('DR_ENABLED', true),

        'recovery_point_objective' => env('DR_RPO', 3600), // seconds (1 hour)
        'recovery_time_objective' => env('DR_RTO', 7200), // seconds (2 hours)

        'failover' => [
            'enabled' => env('DR_FAILOVER_ENABLED', false),
            'automatic' => env('DR_FAILOVER_AUTOMATIC', false),
            
            'secondary_site' => [
                'url' => env('DR_SECONDARY_URL'),
                'database_host' => env('DR_SECONDARY_DB_HOST'),
                'storage_path' => env('DR_SECONDARY_STORAGE_PATH'),
            ],

            'health_check_interval' => env('DR_HEALTH_CHECK_INTERVAL', 60), // seconds
            'failure_threshold' => env('DR_FAILURE_THRESHOLD', 3), // consecutive failures
        ],

        'replication' => [
            'enabled' => env('DR_REPLICATION_ENABLED', false),
            'method' => env('DR_REPLICATION_METHOD', 'async'), // sync, async
            'interval' => env('DR_REPLICATION_INTERVAL', 300), // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Verification
    |--------------------------------------------------------------------------
    */

    'verification' => [
        'enabled' => env('BACKUP_VERIFICATION_ENABLED', true),
        
        'methods' => [
            'checksum' => true, // MD5/SHA256 verification
            'size_check' => true,
            'compression_test' => true,
            'restore_test' => env('BACKUP_RESTORE_TEST', false), // Full restore test
        ],

        'checksum_algorithm' => env('BACKUP_CHECKSUM_ALGORITHM', 'sha256'), // md5, sha1, sha256
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Cleanup
    |--------------------------------------------------------------------------
    */

    'cleanup' => [
        'enabled' => env('BACKUP_CLEANUP_ENABLED', true),
        'schedule' => env('BACKUP_CLEANUP_SCHEDULE', 'daily'),
        
        'strategy' => env('BACKUP_CLEANUP_STRATEGY', 'grandfather-father-son'), // grandfather-father-son, simple
        
        'max_storage_size' => env('BACKUP_MAX_STORAGE_SIZE', 50), // GB
        'min_free_space' => env('BACKUP_MIN_FREE_SPACE', 10), // GB
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Encryption
    |--------------------------------------------------------------------------
    */

    'encryption' => [
        'enabled' => env('BACKUP_ENCRYPTION_ENABLED', false),
        'algorithm' => env('BACKUP_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
        'key' => env('BACKUP_ENCRYPTION_KEY'),
        'key_rotation' => env('BACKUP_KEY_ROTATION_DAYS', 90), // days
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Reporting
    |--------------------------------------------------------------------------
    */

    'monitoring' => [
        'enabled' => env('BACKUP_MONITORING_ENABLED', true),
        
        'metrics' => [
            'backup_size' => true,
            'backup_duration' => true,
            'success_rate' => true,
            'storage_usage' => true,
        ],

        'reports' => [
            'daily_summary' => true,
            'weekly_summary' => true,
            'monthly_summary' => true,
        ],

        'report_recipients' => explode(',', env('BACKUP_REPORT_RECIPIENTS', 'admin@renthub.com')),
    ],

];
