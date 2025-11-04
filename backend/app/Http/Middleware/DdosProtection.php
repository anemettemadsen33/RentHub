<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class DdosProtection
{
    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $key = 'ddos_protection:' . $ip;

        // Check if IP is blocked
        if (Cache::has("blocked:{$key}")) {
            abort(429, 'Too many requests. Please try again later.');
        }

        // Track request count
        $requests = Cache::get($key, 0);
        $requests++;
        Cache::put($key, $requests, now()->addMinutes(1));

        // Block if threshold exceeded (100 requests per minute)
        if ($requests > 100) {
            Cache::put("blocked:{$key}", true, now()->addMinutes(15));
            
            // Log potential DDoS attack
            \Log::warning('Potential DDoS attack detected', [
                'ip' => $ip,
                'requests' => $requests,
                'user_agent' => $request->userAgent(),
            ]);

            abort(429, 'Too many requests. IP blocked for 15 minutes.');
        }

        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', '100');
        $response->headers->set('X-RateLimit-Remaining', max(0, 100 - $requests));

        return $response;
    }
}
