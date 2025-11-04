<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Encryption Configuration
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'at_rest' => [
            'enabled' => env('ENCRYPT_DATA_AT_REST', true),
            'algorithm' => 'aes-256-gcm',
            'key_rotation_days' => 90,
        ],
        'in_transit' => [
            'force_tls' => env('FORCE_TLS', true),
            'min_tls_version' => '1.3',
            'allowed_ciphers' => [
                'TLS_AES_256_GCM_SHA384',
                'TLS_AES_128_GCM_SHA256',
                'TLS_CHACHA20_POLY1305_SHA256',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Protection
    |--------------------------------------------------------------------------
    */
    'data_protection' => [
        'pii_fields' => [
            'email',
            'phone',
            'ssn',
            'tax_id',
            'passport_number',
            'driving_license',
            'date_of_birth',
            'address',
            'bank_account',
            'credit_card',
        ],
        'anonymization' => [
            'enabled' => env('ANONYMIZE_PII', true),
            'method' => 'hash', // hash, mask, redact, pseudonymize
            'retention_days' => 30,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GDPR Compliance
    |--------------------------------------------------------------------------
    */
    'gdpr' => [
        'enabled' => env('GDPR_ENABLED', true),
        'data_retention_days' => env('GDPR_RETENTION_DAYS', 2555), // 7 years
        'deletion_grace_period_days' => 30,
        'export_format' => 'json', // json, csv, pdf
        'consent_tracking' => true,
        'right_to_be_forgotten' => true,
        'data_portability' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | CCPA Compliance
    |--------------------------------------------------------------------------
    */
    'ccpa' => [
        'enabled' => env('CCPA_ENABLED', true),
        'do_not_sell' => true,
        'opt_out_enabled' => true,
        'data_categories' => [
            'identifiers',
            'commercial_information',
            'internet_activity',
            'geolocation',
            'professional_information',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Application Security
    |--------------------------------------------------------------------------
    */
    'app_security' => [
        'sql_injection' => [
            'enabled' => true,
            'use_prepared_statements' => true,
            'validate_input' => true,
        ],
        'xss_protection' => [
            'enabled' => true,
            'sanitize_output' => true,
            'escape_html' => true,
            'content_security_policy' => true,
        ],
        'csrf_protection' => [
            'enabled' => true,
            'token_lifetime' => 7200, // 2 hours
            'per_page_token' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        'driver' => env('RATE_LIMITER_DRIVER', 'redis'), // redis, database, cache
        'defaults' => [
            'api' => [
                'max_attempts' => 60,
                'decay_minutes' => 1,
            ],
            'auth' => [
                'max_attempts' => 5,
                'decay_minutes' => 15,
            ],
            'uploads' => [
                'max_attempts' => 10,
                'decay_minutes' => 60,
            ],
        ],
        'per_user' => [
            'guest' => ['max' => 60, 'decay' => 1],
            'tenant' => ['max' => 120, 'decay' => 1],
            'landlord' => ['max' => 300, 'decay' => 1],
            'admin' => ['max' => 1000, 'decay' => 1],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DDoS Protection
    |--------------------------------------------------------------------------
    */
    'ddos_protection' => [
        'enabled' => env('DDOS_PROTECTION_ENABLED', true),
        'max_requests_per_second' => 10,
        'ban_duration_minutes' => 60,
        'whitelist_ips' => explode(',', env('DDOS_WHITELIST_IPS', '')),
        'blacklist_ips' => explode(',', env('DDOS_BLACKLIST_IPS', '')),
        'challenge_suspicious_traffic' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Headers
    |--------------------------------------------------------------------------
    */
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'geolocation=(self), microphone=(), camera=()',
        'Content-Security-Policy' => implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self' data:",
            "connect-src 'self'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ]),
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation
    |--------------------------------------------------------------------------
    */
    'input_validation' => [
        'enabled' => true,
        'sanitize_strings' => true,
        'strip_tags' => true,
        'max_input_length' => 10000,
        'allowed_file_types' => [
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'archives' => ['zip'],
        ],
        'max_file_size' => 10485760, // 10MB
    ],

    /*
    |--------------------------------------------------------------------------
    | File Upload Security
    |--------------------------------------------------------------------------
    */
    'file_upload' => [
        'scan_for_viruses' => env('SCAN_UPLOADS', true),
        'validate_mime_type' => true,
        'randomize_filenames' => true,
        'store_outside_webroot' => true,
        'max_size' => 10485760, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx'],
        'forbidden_extensions' => ['php', 'exe', 'sh', 'bat', 'cmd', 'com'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Security
    |--------------------------------------------------------------------------
    */
    'session' => [
        'secure' => env('SESSION_SECURE_COOKIE', true),
        'http_only' => true,
        'same_site' => 'strict',
        'lifetime' => 120, // minutes
        'idle_timeout' => 30, // minutes
        'regenerate_on_login' => true,
        'device_fingerprinting' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Security
    |--------------------------------------------------------------------------
    */
    'api' => [
        'versioning' => [
            'enabled' => true,
            'header' => 'Accept',
            'deprecation_notice_versions' => 2,
        ],
        'authentication' => [
            'required' => true,
            'methods' => ['jwt', 'api_key', 'oauth'],
        ],
        'authorization' => [
            'rbac_enabled' => true,
            'check_permissions' => true,
        ],
        'validation' => [
            'strict_mode' => true,
            'fail_on_unknown_fields' => false,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Logging
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_authentication' => true,
        'log_authorization_failures' => true,
        'log_data_access' => true,
        'log_data_modifications' => true,
        'log_admin_actions' => true,
        'retention_days' => 365,
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Monitoring
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        'alert_on_suspicious_activity' => true,
        'alert_channels' => ['email', 'slack', 'sms'],
        'thresholds' => [
            'failed_logins' => 5,
            'rate_limit_violations' => 10,
            'unauthorized_access_attempts' => 3,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Policy
    |--------------------------------------------------------------------------
    */
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_symbols' => true,
        'check_compromised' => true,
        'expiry_days' => 90,
        'prevent_reuse_count' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication
    |--------------------------------------------------------------------------
    */
    'two_factor' => [
        'enabled' => env('2FA_ENABLED', false),
        'enforced_for_roles' => ['admin'],
        'methods' => ['totp', 'sms', 'email'],
        'backup_codes_count' => 10,
    ],
];
