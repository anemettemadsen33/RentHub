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

    'allowed_origins' => array_values(array_filter(array_merge([
        env('FRONTEND_URL', 'http://localhost:3000'),
        'http://127.0.0.1:3000',
        'http://localhost:3001',
        'https://rent-hub-beta.vercel.app', // Current production frontend
        'https://rent-hub-six.vercel.app',  // Alternative frontend
        'https://rent-hub-git-master-madsens-projects.vercel.app', // Vercel project domain
        'https://renthub-tbj7yxj7.on-forge.com', // Production backend domain
        'https://renthub-dji696t0.on-forge.com', // Alternative backend domain
    ],
        // Extra comma-separated origins from env (e.g. https://foo.bar,https://baz.qux)
        array_map('trim', array_filter(explode(',', (string) env('CORS_ALLOWED_ORIGINS', ''))))
    ))),

    'allowed_origins_patterns' => array_merge([
        // Explicit production domains
        '#^https://rent-hub-beta\\.vercel\\.app$#i',
        '#^https://rent-hub-six\\.vercel\\.app$#i',
        '#^https://rent-hub-git-master-madsens-projects\\.vercel\\.app$#i',
        '#^https://renthub-tbj7yxj7\\.on-forge\\.com$#i',
        // Development patterns
        '#^http://localhost(:[0-9]+)?$#i',
        '#^http://127\\.0\\.0\\.1(:[0-9]+)?$#i',
    ], env('APP_ENV') !== 'production' ? [
        // Allow wildcard previews only outside production
        '#^https://[\\w-]+\\.vercel\\.app$#i',
        '#^https://[\\w-]+\\.on-forge\\.com$#i',
    ] : [],
        // Extra regex patterns from env (separate by newlines or commas)
        array_filter(array_map(function ($p) { return trim($p); }, preg_split('/[\n,]+/', (string) env('CORS_ALLOWED_ORIGINS_PATTERNS', ''))))
    ),

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

    'max_age' => 3600,

    'supports_credentials' => true,

];
