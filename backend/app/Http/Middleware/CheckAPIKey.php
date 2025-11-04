<?php

namespace App\Http\Middleware;

use App\Services\Auth\APIKeyService;
use Closure;
use Illuminate\Http\Request;

class CheckAPIKey
{
    protected APIKeyService $apiKeyService;

    public function __construct(APIKeyService $apiKeyService)
    {
        $this->apiKeyService = $apiKeyService;
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        $key = $this->getKeyFromRequest($request);

        if (!$key) {
            return response()->json(['error' => 'API key required'], 401);
        }

        $apiKey = $this->apiKeyService->validateKey($key);

        if (!$apiKey) {
            return response()->json(['error' => 'Invalid or expired API key'], 401);
        }

        // Check IP whitelist
        if (!$this->apiKeyService->isIpAllowed($apiKey, $request->ip())) {
            return response()->json(['error' => 'IP not allowed'], 403);
        }

        // Check permission if specified
        if ($permission && !$this->apiKeyService->hasPermission($apiKey, $permission)) {
            return response()->json(['error' => 'Insufficient permissions'], 403);
        }

        // Set authenticated user
        auth()->setUser($apiKey->user);
        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }

    /**
     * Get API key from request
     */
    protected function getKeyFromRequest(Request $request): ?string
    {
        // Check X-API-Key header
        if ($key = $request->header('X-API-Key')) {
            return $key;
        }

        // Check query parameter
        return $request->query('api_key');
    }
}
