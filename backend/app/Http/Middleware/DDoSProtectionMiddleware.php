<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class DDoSProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.ddos_protection.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip();

        // Check whitelist
        if ($this->isWhitelisted($ip)) {
            return $next($request);
        }

        // Check blacklist
        if ($this->isBlacklisted($ip)) {
            Log::warning('Blocked request from blacklisted IP', ['ip' => $ip]);
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if IP is currently banned
        if ($this->isBanned($ip)) {
            return response()->json([
                'error' => 'Your IP has been temporarily banned due to suspicious activity',
                'retry_after' => $this->getBanTimeRemaining($ip),
            ], 429);
        }

        // Track requests per second
        $requestsPerSecond = $this->getRequestsPerSecond($ip);
        $maxRequestsPerSecond = config('security.ddos_protection.max_requests_per_second', 10);

        if ($requestsPerSecond > $maxRequestsPerSecond) {
            $this->banIp($ip);
            
            Log::warning('DDoS protection triggered - IP banned', [
                'ip' => $ip,
                'requests_per_second' => $requestsPerSecond,
                'threshold' => $maxRequestsPerSecond,
            ]);

            return response()->json([
                'error' => 'Too many requests. Your IP has been temporarily banned.',
                'retry_after' => config('security.ddos_protection.ban_duration_minutes', 60) * 60,
            ], 429);
        }

        $this->trackRequest($ip);

        return $next($request);
    }

    /**
     * Check if IP is whitelisted
     */
    protected function isWhitelisted(string $ip): bool
    {
        $whitelist = config('security.ddos_protection.whitelist_ips', []);
        return in_array($ip, $whitelist);
    }

    /**
     * Check if IP is blacklisted
     */
    protected function isBlacklisted(string $ip): bool
    {
        $blacklist = config('security.ddos_protection.blacklist_ips', []);
        return in_array($ip, $blacklist);
    }

    /**
     * Check if IP is banned
     */
    protected function isBanned(string $ip): bool
    {
        return Cache::has('ddos_ban:' . $ip);
    }

    /**
     * Get ban time remaining
     */
    protected function getBanTimeRemaining(string $ip): int
    {
        $expiresAt = Cache::get('ddos_ban:' . $ip);
        return max(0, $expiresAt - time());
    }

    /**
     * Ban an IP address
     */
    protected function banIp(string $ip): void
    {
        $banDuration = config('security.ddos_protection.ban_duration_minutes', 60);
        $expiresAt = now()->addMinutes($banDuration)->timestamp;
        
        Cache::put('ddos_ban:' . $ip, $expiresAt, now()->addMinutes($banDuration));
    }

    /**
     * Track request
     */
    protected function trackRequest(string $ip): void
    {
        $key = 'ddos_tracker:' . $ip . ':' . now()->format('Y-m-d-H-i-s');
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addSeconds(5));
    }

    /**
     * Get requests per second
     */
    protected function getRequestsPerSecond(string $ip): int
    {
        $count = 0;
        $currentSecond = now()->format('Y-m-d-H-i-s');
        
        for ($i = 0; $i < 1; $i++) {
            $key = 'ddos_tracker:' . $ip . ':' . now()->subSeconds($i)->format('Y-m-d-H-i-s');
            $count += Cache::get($key, 0);
        }

        return $count;
    }
}
