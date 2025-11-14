<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://127.0.0.1:3000',
        'http://localhost:3001',
        'https://rent-hub-beta.vercel.app', // Current production frontend
        'https://rent-hub-six.vercel.app',  // Alternative frontend
        'https://renthub-tbj7yxj7.on-forge.com', // Production backend domain
        'https://renthub-dji696t0.on-forge.com', // Alternative backend domain
    ],

    'allowed_origins_patterns' => array_merge([
        // Explicit production domains
        '#^https://rent-hub-beta\\.vercel\\.app$#i',
        '#^https://rent-hub-six\\.vercel\\.app$#i',
        '#^https://renthub-tbj7yxj7\\.on-forge\\.com$#i',
        // Development patterns
        '#^http://localhost(:[0-9]+)?$#i',
        '#^http://127\\.0\\.0\\.1(:[0-9]+)?$#i',
    ], env('APP_ENV') !== 'production' ? [
        // Allow wildcard previews only outside production
        '#^https://[\\w-]+\\.vercel\\.app$#i',
        '#^https://[\\w-]+\\.on-forge\\.com$#i',
    ] : []),

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

    'max_age' => 3600,

    'supports_credentials' => true,

];
