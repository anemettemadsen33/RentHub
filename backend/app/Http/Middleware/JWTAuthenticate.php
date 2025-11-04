<?php

namespace App\Http\Middleware;

use App\Services\Auth\JWTService;
use Closure;
use Illuminate\Http\Request;
use Exception;

class JWTAuthenticate
{
    protected JWTService $jwtService;

    public function __construct(JWTService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $this->getTokenFromRequest($request);

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $decoded = $this->jwtService->validateAccessToken($token);
            $user = $this->jwtService->getUserFromToken($token);

            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            // Set authenticated user
            auth()->setUser($user);
            $request->attributes->set('jwt_payload', $decoded);

        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid token: ' . $e->getMessage()], 401);
        }

        return $next($request);
    }

    /**
     * Get token from request
     */
    protected function getTokenFromRequest(Request $request): ?string
    {
        // Check Authorization header
        $header = $request->header('Authorization');
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        // Check query parameter
        return $request->query('token');
    }
}
