<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Trust all proxies (Cloudflare / Nginx) so HTTPS detection works in prod
        $middleware->trustProxies(at: '*');
        // In E2E / testing we use stateless token auth; skip Sanctum stateful middleware to avoid CSRF 419s.
        // REMOVED CustomCorsMiddleware - Laravel handles CORS via config/cors.php for consistency
        // Added RobustAuthMiddleware for enhanced authentication validation
        $apiPrepend = [
            \App\Http\Middleware\DebugRequestMiddleware::class, // DEBUG: Log ALL requests
            \App\Http\Middleware\ApiMetricsMiddleware::class,
            // Temporarily disabled for testing - \App\Http\Middleware\EnhancedCorsSecurityMiddleware::class, // Enhanced CORS and security
            // REMOVED RobustAuthMiddleware from global - will be applied to specific routes only
        ];
        // In development, skip CSRF for API to simplify frontend integration
        if (env('APP_ENV') === 'local' || env('APP_ENV') === 'development') {
            // Skip Sanctum stateful middleware in local dev to avoid CSRF issues
        } elseif (env('APP_ENV') !== 'testing') {
            array_unshift($apiPrepend, \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class);
        }
        $middleware->api(prepend: $apiPrepend);

        $middleware->api(append: [
            \App\Http\Middleware\CompressResponse::class,
        ]);

        $middleware->alias([
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
            'role' => \App\Http\Middleware\CheckRole::class,
            'maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
            'api.metrics' => \App\Http\Middleware\ApiMetricsMiddleware::class,
            'compress' => \App\Http\Middleware\CompressResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
