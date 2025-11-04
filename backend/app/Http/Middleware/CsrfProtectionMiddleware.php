<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CsrfProtectionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('security.app_security.csrf_protection.enabled', true)) {
            return $next($request);
        }

        // Skip CSRF check for safe methods
        if ($this->isSafeMethod($request)) {
            return $next($request);
        }

        // Skip CSRF check for API requests with valid authentication
        if ($this->isApiRequest($request) && $request->user()) {
            return $next($request);
        }

        $token = $this->getTokenFromRequest($request);

        if (!$token || !$this->validateToken($token)) {
            return response()->json([
                'error' => 'CSRF token mismatch',
            ], 419);
        }

        return $next($request);
    }

    /**
     * Check if request method is safe
     */
    protected function isSafeMethod(Request $request): bool
    {
        return in_array($request->method(), ['GET', 'HEAD', 'OPTIONS']);
    }

    /**
     * Check if request is API request
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*');
    }

    /**
     * Get CSRF token from request
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // Check header first
        $token = $request->header('X-CSRF-TOKEN');

        // Then check request body
        if (!$token) {
            $token = $request->input('_token');
        }

        return $token;
    }

    /**
     * Validate CSRF token
     */
    protected function validateToken(string $token): bool
    {
        $storedToken = Cache::get('csrf_token:' . $token);

        if (!$storedToken) {
            return false;
        }

        $lifetime = config('security.app_security.csrf_protection.token_lifetime', 7200);
        $tokenAge = now()->timestamp - $storedToken['created_at'];

        if ($tokenAge > $lifetime) {
            Cache::forget('csrf_token:' . $token);
            return false;
        }

        return true;
    }

    /**
     * Generate new CSRF token
     */
    public static function generateToken(): string
    {
        $token = Str::random(40);
        $lifetime = config('security.app_security.csrf_protection.token_lifetime', 7200);

        Cache::put('csrf_token:' . $token, [
            'created_at' => now()->timestamp,
        ], $lifetime);

        return $token;
    }
}
