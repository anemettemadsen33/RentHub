<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Enhanced CORS and Security Middleware
 * 
 * Provides comprehensive security and CORS handling with:
 * - Enhanced CORS validation
 * - Advanced rate limiting with different tiers
 * - Security header management
 * - Request/response logging
 * - IP-based restrictions
 * - User agent validation
 */
class EnhancedCorsSecurityMiddleware
{
    /**
     * Trusted IP ranges (can be expanded based on requirements)
     */
    protected const TRUSTED_IP_RANGES = [
        '127.0.0.0/8',     // Localhost
        '10.0.0.0/8',      // Private networks
        '172.16.0.0/12',   // Private networks
        '192.168.0.0/16',  // Private networks
    ];

    /**
     * Rate limit tiers
     */
    protected const RATE_LIMIT_TIERS = [
        'authenticated' => 300,  // 300 requests per minute
        'guest' => 60,           // 60 requests per minute
        'suspicious' => 30,      // 30 requests per minute
        'blocked' => 5,          // 5 requests per minute
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Skip security validation for public health check endpoints
        if ($this->isPublicRoute($request)) {
            $response = $next($request);
            $this->addCorsHeaders($request, $response);
            $this->addSecurityHeaders($response);
            return $response;
        }

        // Pre-request security checks
        if (!$this->validateRequest($request)) {
            return $this->securityViolationResponse($request);
        }

        // CORS validation
        if (!$this->validateCors($request)) {
            return $this->corsViolationResponse($request);
        }

        // Rate limiting check
        if ($this->isRateLimited($request)) {
            return $this->rateLimitedResponse($request);
        }

        // Process request
        $response = $next($request);

        // Add CORS headers
        $this->addCorsHeaders($request, $response);

        // Add security headers
        $this->addSecurityHeaders($response);

        // Log request/response for audit
        $this->logRequestResponse($request, $response);

        return $response;
    }

    /**
     * Validate incoming request for security violations
     */
    protected function validateRequest(Request $request): bool
    {
        // Validate IP address
        if (!$this->isValidIp($request->ip())) {
            Log::warning('Request from suspicious IP', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'user_agent' => $request->userAgent(),
            ]);
            return false;
        }

        // Validate user agent
        if (!$this->isValidUserAgent($request->userAgent())) {
            Log::warning('Suspicious user agent detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'path' => $request->path(),
            ]);
            return false;
        }

        // Check for common attack patterns
        if ($this->containsAttackPatterns($request)) {
            Log::alert('Potential attack pattern detected', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'input' => $request->all(),
                'user_agent' => $request->userAgent(),
            ]);
            return false;
        }

        return true;
    }

    /**
     * Validate CORS configuration
     */
    protected function validateCors(Request $request): bool
    {
        $origin = $request->headers->get('Origin');
        
        if (!$origin) {
            return true; // Same-origin request
        }

        $allowedOrigins = config('cors.allowed_origins', []);
        $allowedPatterns = config('cors.allowed_origins_patterns', []);

        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            return true;
        }

        // Check pattern match
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                return true;
            }
        }

        Log::warning('CORS validation failed', [
            'origin' => $origin,
            'allowed_origins' => $allowedOrigins,
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        return false;
    }

    /**
     * Check if IP address is valid and not suspicious
     */
    protected function isValidIp(string $ip): bool
    {
        // Basic IP validation
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        // Allow local development IPs
        $localIps = ['127.0.0.1', '::1', 'localhost'];
        if (in_array($ip, $localIps)) {
            return true;
        }

        // Allow private network ranges in development
        if (app()->environment('local', 'development')) {
            $privateRanges = [
                '127.0.0.0/8',     // Localhost
                '10.0.0.0/8',      // Private networks
                '172.16.0.0/12',   // Private networks
                '192.168.0.0/16',  // Private networks
            ];

            foreach ($privateRanges as $range) {
                if ($this->ipInRange($ip, $range)) {
                    return true;
                }
            }
        }

        // Check if IP is in blocked list
        $blockedIps = Cache::get('blocked_ips', []);
        if (in_array($ip, $blockedIps)) {
            return false;
        }

        // Check for known malicious IP ranges (this can be expanded)
        $suspiciousRanges = [
            '0.0.0.0/8',       // Invalid range
            '169.254.0.0/16',  // Link-local
        ];

        foreach ($suspiciousRanges as $range) {
            if ($this->ipInRange($ip, $range)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Validate user agent string
     */
    protected function isValidUserAgent(?string $userAgent): bool
    {
        // Allow empty user agent in development
        if (app()->environment('local', 'development') && empty($userAgent)) {
            return true;
        }

        if (empty($userAgent)) {
            return false;
        }

        // Check for common bot patterns
        $botPatterns = [
            'bot', 'crawler', 'spider', 'scraper', 'curl', 'wget',
            'python', 'java', 'perl', 'ruby', 'go-http-client',
        ];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check for common attack patterns in request
     */
    protected function containsAttackPatterns(Request $request): bool
    {
        $suspiciousPatterns = [
            '/<script.*?>.*?<\/script>/i', // XSS
            '/union.*select/i',             // SQL injection
            '/\.\.\//',                     // Directory traversal
            '/javascript:/i',               // JavaScript injection
            '/data:text\/html/i',           // Data URI injection
        ];

        $content = json_encode($request->all());

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        $key = $this->getRateLimitKey($request);
        $limit = $this->getRateLimit($request);
        
        return RateLimiter::tooManyAttempts($key, $limit);
    }

    /**
     * Get rate limit key based on user and IP
     */
    protected function getRateLimitKey(Request $request): string
    {
        $user = $request->user();
        $ip = $request->ip();
        $tier = $this->getRateLimitTier($request);
        
        $identifier = $user ? "user:{$user->id}" : "ip:{$ip}";
        return "enhanced_cors:{$tier}:{$identifier}";
    }

    /**
     * Get rate limit tier for the request
     */
    protected function getRateLimitTier(Request $request): string
    {
        // Check if IP is suspicious
        if (!$this->isValidIp($request->ip())) {
            return 'suspicious';
        }

        // Check if IP is blocked
        $blockedIps = Cache::get('blocked_ips', []);
        if (in_array($request->ip(), $blockedIps)) {
            return 'blocked';
        }

        // Check user authentication
        return $request->user() ? 'authenticated' : 'guest';
    }

    /**
     * Get rate limit for the request tier
     */
    protected function getRateLimit(Request $request): int
    {
        $tier = $this->getRateLimitTier($request);
        return self::RATE_LIMIT_TIERS[$tier] ?? self::RATE_LIMIT_TIERS['guest'];
    }

    /**
     * Add CORS headers to response
     */
    protected function addCorsHeaders(Request $request, Response $response): void
    {
        $origin = $request->headers->get('Origin');
        
        if (!$origin) {
            return;
        }

        $allowedOrigins = config('cors.allowed_origins', []);
        $allowedPatterns = config('cors.allowed_origins_patterns', []);

        $isAllowed = false;

        // Check exact match
        if (in_array($origin, $allowedOrigins)) {
            $isAllowed = true;
        }

        // Check pattern match
        foreach ($allowedPatterns as $pattern) {
            if (preg_match($pattern, $origin)) {
                $isAllowed = true;
                break;
            }
        }

        if ($isAllowed) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, X-CSRF-Token');
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
            $response->headers->set('Access-Control-Max-Age', '3600');

            // Add additional CORS headers for enhanced security
            $response->headers->set('Access-Control-Expose-Headers', 'Authorization, X-RateLimit-Limit, X-RateLimit-Remaining');
        }
    }

    /**
     * Add security headers to response
     */
    protected function addSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('X-RateLimit-Limit', (string) $this->getRateLimit(request()));
        $response->headers->set('X-RateLimit-Remaining', (string) RateLimiter::remaining($this->getRateLimitKey(request()), $this->getRateLimit(request())));
        
        // Remove server information
        $response->headers->remove('X-Powered-By');
        
        // Add Content Security Policy (CSP) for API responses
        $response->headers->set('Content-Security-Policy', "default-src 'none'; frame-ancestors 'none';");
        
        // Add Strict Transport Security (HSTS) for HTTPS
        if (request()->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
    }

    /**
     * Log request and response for audit
     */
    protected function logRequestResponse(Request $request, Response $response): void
    {
        $context = [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'status' => $response->getStatusCode(),
            'duration' => microtime(true) - LARAVEL_START,
        ];

        if ($request->user()) {
            $context['user_id'] = $request->user()->id;
            $context['user_email'] = $request->user()->email;
        }

        // Log different levels based on status code
        if ($response->getStatusCode() >= 500) {
            Log::error('Server error', $context);
        } elseif ($response->getStatusCode() >= 400) {
            Log::warning('Client error', $context);
        } else {
            Log::info('Request completed', $context);
        }
    }

    /**
     * Check if request is for a public route that should bypass security validation
     */
    protected function isPublicRoute(Request $request): bool
    {
        $publicRoutes = [
            'health',
            'health/liveness',
            'health/readiness',
            'health/production',
            'health/status',
            'metrics',
            'metrics/prometheus',
            'api/v1/settings/public',
            'api/v1/languages',
            'api/v1/languages/default',
            'api/v1/languages/*',
            'api/v1/currencies',
            'api/v1/currencies/active',
            'api/v1/currencies/default',
            'api/v1/currencies/*',
            'api/v1/properties',
            'api/v1/amenities',
            'api/v1/analytics/web-vitals',
            'api/v1/analytics/pwa',
            'api/v1/oauth/token',
            'api/v1/gdpr/data-protection',
            'api/v1/settings/public',
            'api/v1/performance/recommendations',
        ];

        $currentPath = trim($request->path(), '/');

        foreach ($publicRoutes as $route) {
            // Convert route pattern to regex
            $pattern = str_replace('*', '.*', $route);
            $pattern = '#^' . $pattern . '$#';
            
            if (preg_match($pattern, $currentPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    protected function ipInRange(string $ip, string $range): bool
    {
        list($subnet, $mask) = explode('/', $range);
        return (ip2long($ip) >> (32 - $mask)) === (ip2long($subnet) >> (32 - $mask));
    }

    /**
     * Return security violation response
     */
    protected function securityViolationResponse(Request $request): Response
    {
        return response()->json([
            'error' => 'Security Violation',
            'message' => 'Request blocked due to security policy violation',
            'timestamp' => now()->toDateTimeString(),
            'request_id' => uniqid('security_'),
        ], 403);
    }

    /**
     * Return CORS violation response
     */
    protected function corsViolationResponse(Request $request): Response
    {
        return response()->json([
            'error' => 'CORS Policy Violation',
            'message' => 'Cross-origin request not allowed',
            'timestamp' => now()->toDateTimeString(),
            'request_id' => uniqid('cors_'),
        ], 403);
    }

    /**
     * Return rate limited response
     */
    protected function rateLimitedResponse(Request $request): Response
    {
        $key = $this->getRateLimitKey($request);
        $retryAfter = RateLimiter::availableIn($key);

        Log::warning('Rate limit exceeded', [
            'key' => $key,
            'retry_after' => $retryAfter,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
        ]);

        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter,
            'available_in' => $retryAfter . ' seconds',
            'timestamp' => now()->toDateTimeString(),
        ], 429)->header('Retry-After', $retryAfter);
    }
}