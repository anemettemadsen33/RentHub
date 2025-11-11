<?php

namespace App\Http\Middleware;

use App\Services\MetricsService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiMetricsMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = microtime(true);
        $response = $next($request);
        $durationMs = (microtime(true) - $start) * 1000;

        // Basic inline metric logging
        Log::info('api.request', [
            'path' => $request->path(),
            'method' => $request->method(),
            'status' => $response->getStatusCode(),
            'duration_ms' => (int) round($durationMs),
            'user_id' => optional($request->user())->id,
        ]);

        // Record metrics
        try {
            /** @var MetricsService $metrics */
            $metrics = App::make(MetricsService::class);

            $route = $request->route() ? $request->route()->getName() ?? $request->path() : $request->path();
            $method = $request->method();
            $status = (string) $response->getStatusCode();

            // Counter: total requests by route, method, status
            $metrics->incrementCounter('http_requests_total', 1, [
                'route' => $route,
                'method' => $method,
                'status' => $status,
            ]);

            // Histogram: request duration by route
            $metrics->recordHistogram('http_request_duration_ms', $durationMs, [
                'route' => $route,
                'method' => $method,
            ]);
        } catch (\Throwable $e) {
            // Silent failure - don't break requests due to metrics
            Log::warning('Failed to record metrics', ['error' => $e->getMessage()]);
        }

        $response->headers->set('X-Response-Time-ms', (string) (int) round($durationMs));

        return $response;
    }
}
