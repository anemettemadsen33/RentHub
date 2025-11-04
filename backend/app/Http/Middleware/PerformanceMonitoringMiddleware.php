<?php

namespace App\Http\Middleware;

use App\Services\Performance\MonitoringService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoringMiddleware
{
    protected MonitoringService $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = memory_get_usage() - $startMemory;

        // Record metrics
        $this->monitoringService->recordResponseTime($duration);

        // Add performance headers in development
        if (config('app.debug')) {
            $response->headers->set('X-Response-Time', round($duration, 2) . 'ms');
            $response->headers->set('X-Memory-Usage', round($memoryUsed / 1024, 2) . 'KB');
        }

        // Log slow requests
        if ($duration > 1000) { // Requests taking more than 1 second
            \Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'duration' => round($duration, 2) . 'ms',
                'memory' => round($memoryUsed / 1024, 2) . 'KB',
            ]);
        }

        return $response;
    }
}
