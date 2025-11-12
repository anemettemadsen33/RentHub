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
        'https://rent-hub-six.vercel.app',
        'https://rent-hub-beta.vercel.app', // Production frontend
        'null', // For local file:// testing only - remove in production
    ],

    'allowed_origins_patterns' => [
        // Matches renthub.com or any subdomain (www.renthub.com, api.renthub.com, etc.)
        '#^https?://([\w-]+\.)?renthub\.com$#i',
        // Matches any Vercel deployment (requires subdomain: your-app.vercel.app)
        '#^https?://[\w-]+\.vercel\.app$#i',
        // Matches any Forge deployment (requires subdomain: your-site.on-forge.com)
        '#^https?://[\w-]+\.on-forge\.com$#i',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization', 'Content-Type', 'X-Requested-With'],

    'max_age' => 3600,

    'supports_credentials' => true,

];
