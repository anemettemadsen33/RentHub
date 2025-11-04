<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain user data after account deletion
    |
    */
    'data_retention_days' => env('GDPR_DATA_RETENTION_DAYS', 365),

    /*
    |--------------------------------------------------------------------------
    | Anonymization Settings
    |--------------------------------------------------------------------------
    |
    | Configure what happens during user anonymization
    |
    */
    'anonymization' => [
        'keep_bookings' => true,
        'keep_reviews' => true,
        'keep_properties' => false,
        'anonymize_reviews' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Data Export Settings
    |--------------------------------------------------------------------------
    |
    | Configure data export functionality
    |
    */
    'export' => [
        'format' => 'json', // json, csv, xml
        'include_files' => true,
        'max_file_size' => 100, // MB
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent Settings
    |--------------------------------------------------------------------------
    |
    | Configure consent management
    |
    */
    'consent' => [
        'required_for_registration' => true,
        'cookie_consent_required' => true,
        'marketing_consent_required' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | PII Fields
    |--------------------------------------------------------------------------
    |
    | Define which fields contain personally identifiable information
    |
    */
    'pii_fields' => [
        'users' => ['name', 'email', 'phone', 'address', 'date_of_birth', 'ssn'],
        'bookings' => ['guest_name', 'guest_email', 'guest_phone'],
        'reviews' => ['user_name'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Encryption Settings
    |--------------------------------------------------------------------------
    |
    | Fields that should be encrypted at rest
    |
    */
    'encrypted_fields' => [
        'users' => ['ssn', 'payment_info'],
        'bookings' => ['guest_phone'],
    ],
];
