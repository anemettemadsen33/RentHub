<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AdvancedRateLimitMiddleware
{
    /**
     * Rate limit configurations for different endpoints
     */
    protected array $limits = [
        'login' => ['attempts' => 5, 'decay' => 300],
        'register' => ['attempts' => 3, 'decay' => 600],
        'password-reset' => ['attempts' => 3, 'decay' => 900],
        'api' => ['attempts' => 100, 'decay' => 60],
        'booking' => ['attempts' => 10, 'decay' => 60],
        'search' => ['attempts' => 60, 'decay' => 60],
    ];

    /**
     * DDoS protection thresholds
     */
    protected array $ddosProtection = [
        'requests_per_second' => 20,
        'burst_threshold' => 100,
        'ban_duration' => 3600,
    ];

    public function handle(Request $request, Closure $next, string $type = 'api'): Response
    {
        $identifier = $this->getIdentifier($request);
        
        // Check if IP is banned
        if ($this->isBanned($identifier)) {
            Log::warning('Banned IP attempted access', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            
            return response()->json([
                'error' => 'Too many requests. Your IP has been temporarily banned.',
            ], 429);
        }

        // DDoS protection
        if ($this->isDDoSAttempt($identifier)) {
            $this->banIdentifier($identifier);
            
            Log::critical('Possible DDoS attack detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            
            return response()->json([
                'error' => 'Too many requests. Access denied.',
            ], 429);
        }

        // Standard rate limiting
        $limit = $this->limits[$type] ?? $this->limits['api'];
        $key = "rate_limit:{$type}:{$identifier}";
        
        $attempts = Cache::get($key, 0);
        
        if ($attempts >= $limit['attempts']) {
            $retryAfter = Cache::get($key . ':expires') - time();
            
            return response()->json([
                'error' => 'Rate limit exceeded. Please try again later.',
                'retry_after' => max($retryAfter, 0),
            ], 429)->header('Retry-After', max($retryAfter, 0));
        }

        // Increment attempt counter
        if ($attempts === 0) {
            Cache::put($key, 1, $limit['decay']);
            Cache::put($key . ':expires', time() + $limit['decay'], $limit['decay']);
        } else {
            Cache::increment($key);
        }

        $response = $next($request);

        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $limit['attempts']);
        $response->headers->set('X-RateLimit-Remaining', max(0, $limit['attempts'] - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', Cache::get($key . ':expires', time()));

        return $response;
    }

    protected function getIdentifier(Request $request): string
    {
        $user = $request->user();
        if ($user) {
            return 'user:' . $user->id;
        }
        
        return 'ip:' . $request->ip();
    }

    protected function isBanned(string $identifier): bool
    {
        return Cache::has("banned:{$identifier}");
    }

    protected function banIdentifier(string $identifier): void
    {
        Cache::put(
            "banned:{$identifier}",
            true,
            $this->ddosProtection['ban_duration']
        );
    }

    protected function isDDoSAttempt(string $identifier): bool
    {
        $key = "ddos_check:{$identifier}";
        $burstKey = "ddos_burst:{$identifier}";
        
        // Track requests per second
        $requestsThisSecond = Cache::get($key, 0);
        if ($requestsThisSecond === 0) {
            Cache::put($key, 1, 1);
        } else {
            Cache::increment($key);
            $requestsThisSecond++;
        }
        
        // Track burst requests
        $burstRequests = Cache::get($burstKey, 0);
        if ($burstRequests === 0) {
            Cache::put($burstKey, 1, 10);
        } else {
            Cache::increment($burstKey);
            $burstRequests++;
        }
        
        return $requestsThisSecond > $this->ddosProtection['requests_per_second'] ||
               $burstRequests > $this->ddosProtection['burst_threshold'];
    }
}
