<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Monitoring Provider
    |--------------------------------------------------------------------------
    |
    | Select the monitoring provider (newrelic, datadog, prometheus)
    |
    */

    'provider' => env('MONITORING_PROVIDER', 'datadog'),
    'enabled' => env('MONITORING_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | New Relic Configuration
    |--------------------------------------------------------------------------
    */

    'newrelic' => [
        'enabled' => env('NEWRELIC_ENABLED', false),
        'license_key' => env('NEWRELIC_LICENSE_KEY'),
        'app_name' => env('NEWRELIC_APP_NAME', 'RentHub'),

        'transaction_tracer' => [
            'enabled' => true,
            'threshold' => 'apdex_f',
            'record_sql' => 'obfuscated',
        ],

        'error_collector' => [
            'enabled' => true,
            'ignore_status_codes' => [401, 404],
        ],

        'browser_monitoring' => [
            'enabled' => true,
            'auto_instrument' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DataDog Configuration
    |--------------------------------------------------------------------------
    */

    'datadog' => [
        'enabled' => env('DATADOG_ENABLED', false),
        'api_key' => env('DATADOG_API_KEY'),
        'app_key' => env('DATADOG_APP_KEY'),
        'host' => env('DATADOG_HOST', 'api.datadoghq.com'),

        'apm' => [
            'enabled' => true,
            'service_name' => env('DATADOG_SERVICE_NAME', 'renthub'),
            'env' => env('DATADOG_ENV', 'production'),
            'version' => env('APP_VERSION', '1.0.0'),
        ],

        'metrics' => [
            'enabled' => true,
            'namespace' => 'renthub',
            'flush_interval' => 10, // seconds
        ],

        'logs' => [
            'enabled' => true,
            'source' => 'laravel',
            'service' => 'renthub-api',
        ],

        'trace' => [
            'enabled' => true,
            'sample_rate' => env('DATADOG_TRACE_SAMPLE_RATE', 1.0),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Prometheus Configuration
    |--------------------------------------------------------------------------
    */

    'prometheus' => [
        'enabled' => env('PROMETHEUS_ENABLED', false),
        'namespace' => env('PROMETHEUS_NAMESPACE', 'renthub'),
        'metrics_route' => env('PROMETHEUS_METRICS_ROUTE', '/metrics'),

        'collectors' => [
            'default' => true,
            'requests' => true,
            'database' => true,
            'cache' => true,
            'queue' => true,
        ],

        'buckets' => [
            'request_duration' => [0.005, 0.01, 0.025, 0.05, 0.075, 0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sentry Configuration (Error Tracking)
    |--------------------------------------------------------------------------
    */

    'sentry' => [
        'enabled' => env('SENTRY_ENABLED', false),
        'dsn' => env('SENTRY_LARAVEL_DSN'),
        'environment' => env('SENTRY_ENVIRONMENT', env('APP_ENV', 'production')),
        'release' => env('SENTRY_RELEASE', env('APP_VERSION')),

        'traces_sample_rate' => env('SENTRY_TRACES_SAMPLE_RATE', 0.1),
        'profiles_sample_rate' => env('SENTRY_PROFILES_SAMPLE_RATE', 0.1),

        'send_default_pii' => false,
        'attach_stacktrace' => true,
        'before_send' => null,

        'breadcrumbs' => [
            'sql_queries' => true,
            'sql_bindings' => true,
            'queue_info' => true,
            'command_info' => true,
        ],

        'integrations' => [
            'laravel' => true,
            'query' => true,
            'redis' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Metrics
    |--------------------------------------------------------------------------
    */

    'metrics' => [
        'enabled' => env('METRICS_ENABLED', true),

        'collect' => [
            'requests' => true,
            'exceptions' => true,
            'database_queries' => true,
            'cache_operations' => true,
            'queue_jobs' => true,
            'external_api_calls' => true,
        ],

        'custom_metrics' => [
            'properties_viewed' => true,
            'bookings_created' => true,
            'payments_processed' => true,
            'searches_performed' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Aggregation Configuration
    |--------------------------------------------------------------------------
    */

    'log_aggregation' => [
        'enabled' => env('LOG_AGGREGATION_ENABLED', false),
        'provider' => env('LOG_AGGREGATION_PROVIDER', 'elk'), // elk, splunk, cloudwatch

        'elk' => [
            'elasticsearch' => [
                'hosts' => env('ELASTICSEARCH_HOSTS', 'localhost:9200'),
                'index_prefix' => env('ELASTICSEARCH_INDEX_PREFIX', 'renthub'),
                'index_pattern' => env('ELASTICSEARCH_INDEX_PATTERN', 'daily'),
            ],
            'logstash' => [
                'host' => env('LOGSTASH_HOST', 'localhost'),
                'port' => env('LOGSTASH_PORT', 5044),
            ],
        ],

        'splunk' => [
            'url' => env('SPLUNK_URL'),
            'token' => env('SPLUNK_TOKEN'),
            'index' => env('SPLUNK_INDEX', 'renthub'),
        ],

        'cloudwatch' => [
            'group' => env('CLOUDWATCH_LOG_GROUP', 'renthub'),
            'stream' => env('CLOUDWATCH_LOG_STREAM', 'application'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Uptime Monitoring
    |--------------------------------------------------------------------------
    */

    'uptime' => [
        'enabled' => env('UPTIME_MONITORING_ENABLED', true),
        'providers' => [
            'pingdom' => [
                'enabled' => env('PINGDOM_ENABLED', false),
                'api_key' => env('PINGDOM_API_KEY'),
                'check_ids' => env('PINGDOM_CHECK_IDS', ''),
            ],
            'uptimerobot' => [
                'enabled' => env('UPTIMEROBOT_ENABLED', false),
                'api_key' => env('UPTIMEROBOT_API_KEY'),
                'monitor_ids' => env('UPTIMEROBOT_MONITOR_IDS', ''),
            ],
            'statuspage' => [
                'enabled' => env('STATUSPAGE_ENABLED', false),
                'page_id' => env('STATUSPAGE_PAGE_ID'),
                'api_key' => env('STATUSPAGE_API_KEY'),
            ],
        ],

        'checks' => [
            'http' => [
                'enabled' => true,
                'urls' => [
                    env('APP_URL'),
                    env('APP_URL').'/api/health',
                ],
                'interval' => 60, // seconds
                'timeout' => 10,
            ],
            'ssl' => [
                'enabled' => true,
                'days_before_expiry_alert' => 30,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring
    |--------------------------------------------------------------------------
    */

    'performance' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),

        'thresholds' => [
            'slow_query' => env('SLOW_QUERY_THRESHOLD', 1000), // milliseconds
            'slow_request' => env('SLOW_REQUEST_THRESHOLD', 2000), // milliseconds
            'high_memory' => env('HIGH_MEMORY_THRESHOLD', 512), // MB
        ],

        'profiling' => [
            'enabled' => env('PROFILING_ENABLED', false),
            'sample_rate' => env('PROFILING_SAMPLE_RATE', 0.01),
            'storage' => env('PROFILING_STORAGE', 'redis'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerting Configuration
    |--------------------------------------------------------------------------
    */

    'alerts' => [
        'enabled' => env('ALERTS_ENABLED', true),

        'channels' => [
            'slack' => [
                'enabled' => env('SLACK_ALERTS_ENABLED', false),
                'webhook_url' => env('SLACK_WEBHOOK_URL'),
                'channel' => env('SLACK_ALERT_CHANNEL', '#alerts'),
            ],
            'email' => [
                'enabled' => env('EMAIL_ALERTS_ENABLED', true),
                'recipients' => explode(',', env('ALERT_EMAIL_RECIPIENTS', '')),
            ],
            'pagerduty' => [
                'enabled' => env('PAGERDUTY_ENABLED', false),
                'integration_key' => env('PAGERDUTY_INTEGRATION_KEY'),
            ],
            'opsgenie' => [
                'enabled' => env('OPSGENIE_ENABLED', false),
                'api_key' => env('OPSGENIE_API_KEY'),
            ],
        ],

        'rules' => [
            'high_error_rate' => [
                'threshold' => 5, // percent
                'window' => 300, // seconds
                'severity' => 'critical',
            ],
            'slow_responses' => [
                'threshold' => 3000, // milliseconds
                'count' => 10,
                'window' => 300,
                'severity' => 'warning',
            ],
            'high_cpu' => [
                'threshold' => 80, // percent
                'duration' => 600,
                'severity' => 'warning',
            ],
            'high_memory' => [
                'threshold' => 85, // percent
                'duration' => 600,
                'severity' => 'critical',
            ],
            'database_connection_errors' => [
                'threshold' => 3,
                'window' => 60,
                'severity' => 'critical',
            ],
        ],
    ],

];
