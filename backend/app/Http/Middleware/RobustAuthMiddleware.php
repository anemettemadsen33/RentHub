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
 * Robust Authentication Middleware
 * 
 * Provides comprehensive authentication validation with:
 * - Token validation and refresh
 * - Rate limiting per user/IP
 * - Session management
 * - Comprehensive logging
 * - Security headers
 * - Multi-guard support
 * - Security headers
 */
class RobustAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // Validate authentication
        if (!$this->validateAuthentication($request, $guards)) {
            return $this->unauthenticatedResponse($request);
        }

        // Rate limiting check
        if ($this->isRateLimited($request)) {
            return $this->rateLimitedResponse($request);
        }

        // Log authenticated request
        $this->logAuthenticatedRequest($request);

        // Process request
        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        // Update session activity
        $this->updateSessionActivity($request);

        return $response;
    }

    /**
     * Validate authentication for the request
     */
    protected function validateAuthentication(Request $request, array $guards): bool
    {
        // Use default guard if none specified
        if (empty($guards)) {
            $guards = [config('auth.defaults.guard', 'sanctum')];
        }

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // Set the default guard to the working one
                Auth::shouldUse($guard);
                
                // Validate token if using Sanctum
                if ($guard === 'sanctum' && $request->user()) {
                    return $this->validateSanctumToken($request);
                }
                
                return true;
            }
        }

        return false;
    }

    /**
     * Validate Sanctum token specifics
     */
    protected function validateSanctumToken(Request $request): bool
    {
        $user = $request->user();
        $token = $request->user()->currentAccessToken();

        if (!$token) {
            Log::warning('Sanctum token validation failed: No current access token', [
                'user_id' => $user->id,
                'request_ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return false;
        }

        // Check token expiration if configured
        if (config('sanctum.expiration')) {
            $expiresAt = $token->created_at->addMinutes(config('sanctum.expiration'));
            if (now()->greaterThan($expiresAt)) {
                Log::info('Sanctum token expired', [
                    'user_id' => $user->id,
                    'token_id' => $token->id,
                    'expires_at' => $expiresAt->toDateTimeString(),
                ]);
                
                // Revoke expired token
                $token->delete();
                return false;
            }
        }

        // Update token last used timestamp
        $token->forceFill(['last_used_at' => now()])->save();

        return true;
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        $key = $this->getRateLimitKey($request);
        
        // Use Laravel's rate limiter
        return RateLimiter::tooManyAttempts($key, $this->getRateLimit($request));
    }

    /**
     * Get rate limit key based on user and IP
     */
    protected function getRateLimitKey(Request $request): string
    {
        $user = $request->user();
        $identifier = $user ? "user:{$user->id}" : "ip:{$request->ip()}";
        
        return "auth:{$identifier}";
    }

    /**
     * Get rate limit for the request
     */
    protected function getRateLimit(Request $request): int
    {
        // Higher limit for authenticated users
        return $request->user() ? 300 : 60; // per minute
    }

    /**
     * Log authenticated request
     */
    protected function logAuthenticatedRequest(Request $request): void
    {
        $user = $request->user();
        
        Log::info('Authenticated request', [
            'user_id' => $user->id,
            'email' => $user->email,
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Cache recent activity for session management
        if ($user) {
            Cache::put(
                "user_activity:{$user->id}",
                [
                    'last_request_at' => now(),
                    'last_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                3600 // 1 hour
            );
        }
    }

    /**
     * Update session activity
     */
    protected function updateSessionActivity(Request $request): void
    {
        $user = $request->user();
        
        if ($user) {
            // Update user's last activity timestamp
            $user->forceFill(['last_active_at' => now()])->save();
            
            // Update session in cache
            Cache::put(
                "user_session:{$user->id}",
                [
                    'user_id' => $user->id,
                    'last_activity' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
                86400 // 24 hours
            );
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
        
        // Remove server information
        $response->headers->remove('X-Powered-By');
    }

    /**
     * Return unauthenticated response
     */
    protected function unauthenticatedResponse(Request $request): Response
    {
        Log::warning('Unauthenticated request', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'error' => 'Unauthenticated',
            'message' => 'Authentication required. Please provide a valid token.',
            'timestamp' => now()->toDateTimeString(),
        ], 401);
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