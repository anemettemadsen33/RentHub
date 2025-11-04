<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class APIGatewayMiddleware
{
    /**
     * Advanced API Gateway with security features
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. API Key Validation
        if (!$this->validateApiKey($request)) {
            return response()->json(['error' => 'Invalid or missing API key'], 401);
        }

        // 2. Rate Limiting (per API key)
        if (!$this->checkRateLimit($request)) {
            return response()->json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $this->getRetryAfter($request),
            ], 429);
        }

        // 3. Request Validation
        if (!$this->validateRequest($request)) {
            return response()->json(['error' => 'Invalid request format'], 400);
        }

        // 4. IP Whitelisting/Blacklisting
        if (!$this->checkIPAccess($request)) {
            return response()->json(['error' => 'Access denied from this IP'], 403);
        }

        // 5. Request Signing Verification
        if (config('api.require_signature') && !$this->verifySignature($request)) {
            return response()->json(['error' => 'Invalid request signature'], 401);
        }

        // 6. Log API Request
        $this->logApiRequest($request);

        $response = $next($request);

        // 7. Add security headers
        $response->headers->set('X-API-Version', config('app.api_version', 'v1'));
        $response->headers->set('X-Request-ID', $request->id());

        // 8. Response filtering (remove sensitive data)
        $this->filterResponse($response);

        return $response;
    }

    /**
     * Validate API Key
     */
    private function validateApiKey(Request $request): bool
    {
        $apiKey = $request->header('X-API-Key') ?? $request->query('api_key');

        if (!$apiKey) {
            return false;
        }

        // Check in cache first
        $cacheKey = "api_key:valid:{$apiKey}";
        
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Validate against database
        $isValid = \App\Models\ApiKey::where('key', hash('sha256', $apiKey))
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        Cache::put($cacheKey, $isValid, 300);

        if ($isValid) {
            // Store API key metadata in request
            $request->merge(['api_key_validated' => true]);
        }

        return $isValid;
    }

    /**
     * Advanced rate limiting with multiple strategies
     */
    private function checkRateLimit(Request $request): bool
    {
        $apiKey = $request->header('X-API-Key');
        $endpoint = $request->route()->getName();

        // Get rate limit config for this endpoint
        $limits = $this->getRateLimits($endpoint);

        foreach ($limits as $window => $maxAttempts) {
            $key = "rate_limit:{$apiKey}:{$endpoint}:{$window}";
            
            if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
                Log::warning('API Gateway: Rate limit exceeded', [
                    'api_key' => substr($apiKey, 0, 8) . '...',
                    'endpoint' => $endpoint,
                    'window' => $window,
                    'ip' => $request->ip(),
                ]);
                
                return false;
            }

            RateLimiter::hit($key, $this->getWindowSeconds($window));
        }

        return true;
    }

    /**
     * Get rate limits for endpoint
     */
    private function getRateLimits(string $endpoint): array
    {
        $config = config('api.rate_limits', []);

        // Check endpoint-specific limits
        if (isset($config[$endpoint])) {
            return $config[$endpoint];
        }

        // Default limits
        return [
            'minute' => 60,
            'hour' => 1000,
            'day' => 10000,
        ];
    }

    /**
     * Convert window to seconds
     */
    private function getWindowSeconds(string $window): int
    {
        return match ($window) {
            'second' => 1,
            'minute' => 60,
            'hour' => 3600,
            'day' => 86400,
            default => 60,
        };
    }

    /**
     * Get retry after seconds
     */
    private function getRetryAfter(Request $request): int
    {
        $apiKey = $request->header('X-API-Key');
        $endpoint = $request->route()->getName();
        $key = "rate_limit:{$apiKey}:{$endpoint}:minute";

        return RateLimiter::availableIn($key);
    }

    /**
     * Validate request format
     */
    private function validateRequest(Request $request): bool
    {
        // Check content type for POST/PUT/PATCH
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $contentType = $request->header('Content-Type');
            
            if (!str_contains($contentType, 'application/json')) {
                return false;
            }
        }

        // Check request size
        if ($request->header('Content-Length') > config('api.max_request_size', 10485760)) {
            return false;
        }

        return true;
    }

    /**
     * Check IP access control
     */
    private function checkIPAccess(Request $request): bool
    {
        $ip = $request->ip();

        // Check blacklist
        $blacklist = Cache::remember('ip_blacklist', 300, function () {
            return \App\Models\IPBlacklist::pluck('ip_address')->toArray();
        });

        if (in_array($ip, $blacklist)) {
            Log::warning('API Gateway: Blacklisted IP access attempt', [
                'ip' => $ip,
                'endpoint' => $request->route()->getName(),
            ]);
            return false;
        }

        // Check whitelist (if enabled)
        if (config('api.whitelist_enabled')) {
            $whitelist = Cache::remember('ip_whitelist', 300, function () {
                return \App\Models\IPWhitelist::pluck('ip_address')->toArray();
            });

            return in_array($ip, $whitelist);
        }

        return true;
    }

    /**
     * Verify request signature
     */
    private function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Signature');
        $timestamp = $request->header('X-Timestamp');

        if (!$signature || !$timestamp) {
            return false;
        }

        // Prevent replay attacks (5 minute window)
        if (abs(time() - $timestamp) > 300) {
            return false;
        }

        $apiKey = $request->header('X-API-Key');
        $apiSecret = $this->getApiSecret($apiKey);

        if (!$apiSecret) {
            return false;
        }

        // Compute expected signature
        $payload = $request->method() . $request->getPathInfo() . $timestamp . $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $apiSecret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get API secret for key
     */
    private function getApiSecret(string $apiKey): ?string
    {
        return Cache::remember("api_secret:{$apiKey}", 300, function () use ($apiKey) {
            return \App\Models\ApiKey::where('key', hash('sha256', $apiKey))
                ->value('secret');
        });
    }

    /**
     * Log API request
     */
    private function logApiRequest(Request $request): void
    {
        $logData = [
            'api_key' => substr($request->header('X-API-Key'), 0, 8) . '...',
            'method' => $request->method(),
            'endpoint' => $request->route()->getName(),
            'path' => $request->getPathInfo(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_id' => $request->id(),
        ];

        Log::info('API Gateway: Request', $logData);

        // Store in metrics database for analytics
        \App\Models\ApiMetric::create($logData);
    }

    /**
     * Filter sensitive data from response
     */
    private function filterResponse($response): void
    {
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $data = $response->getData(true);
            
            // Remove sensitive fields
            $sensitiveFields = ['password', 'secret', 'token', 'api_key', 'ssn'];
            
            array_walk_recursive($data, function (&$value, $key) use ($sensitiveFields) {
                if (in_array(strtolower($key), $sensitiveFields)) {
                    $value = '[FILTERED]';
                }
            });

            $response->setData($data);
        }
    }
}
