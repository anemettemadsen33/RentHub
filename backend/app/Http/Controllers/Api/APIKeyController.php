<?php

namespace App\Http\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Services\Auth\APIKeyService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class APIKeyController extends Controller
{
    public function __construct(
        protected APIKeyService $apiKeyService
    ) {}

    /**
     * Get all API keys for authenticated user
     */
    public function index(Request $request)
    {
        $keys = $this->apiKeyService->getUserKeys(auth()->user());

        return response()->json([
            'api_keys' => $keys,
        ]);
    }

    /**
     * Create new API key
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'expires_at' => 'nullable|date|after:now',
            'ip_whitelist' => 'nullable|string',
        ]);

        $apiKey = $this->apiKeyService->createKey(
            auth()->user(),
            $validated['name'],
            $validated['permissions'] ?? null,
            isset($validated['expires_at']) ? Carbon::parse($validated['expires_at']) : null,
            $validated['ip_whitelist'] ?? null
        );

        return response()->json([
            'api_key' => $apiKey,
            'plain_key' => $apiKey->plain_key, // Only shown once
            'message' => 'API key created successfully. Make sure to save the key, it won\'t be shown again.',
        ], 201);
    }

    /**
     * Update API key
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        // Check ownership
        if ($apiKey->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
            'expires_at' => 'nullable|date|after:now',
            'ip_whitelist' => 'nullable|string',
            'active' => 'sometimes|boolean',
        ]);

        if (isset($validated['expires_at'])) {
            $validated['expires_at'] = Carbon::parse($validated['expires_at']);
        }

        $apiKey->update($validated);

        return response()->json([
            'api_key' => $apiKey,
            'message' => 'API key updated successfully',
        ]);
    }

    /**
     * Revoke API key
     */
    public function destroy(ApiKey $apiKey)
    {
        // Check ownership
        if ($apiKey->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $this->apiKeyService->revokeKey($apiKey);

        return response()->json([
            'message' => 'API key revoked successfully',
        ]);
    }

    /**
     * Rotate API key
     */
    public function rotate(ApiKey $apiKey)
    {
        // Check ownership
        if ($apiKey->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        $newKey = $this->apiKeyService->rotateKey($apiKey);

        return response()->json([
            'api_key' => $newKey,
            'plain_key' => $newKey->plain_key,
            'message' => 'API key rotated successfully',
        ]);
    }

    /**
     * Get API key statistics
     */
    public function stats(ApiKey $apiKey)
    {
        // Check ownership
        if ($apiKey->user_id !== auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
            ], 403);
        }

        return response()->json([
            'stats' => [
                'usage_count' => $apiKey->usage_count,
                'last_used_at' => $apiKey->last_used_at,
                'created_at' => $apiKey->created_at,
                'expires_at' => $apiKey->expires_at,
                'is_active' => $apiKey->active,
                'is_expired' => $apiKey->is_expired,
                'days_until_expiry' => $apiKey->expires_at
                    ? now()->diffInDays($apiKey->expires_at, false)
                    : null,
            ],
        ]);
    }
}

