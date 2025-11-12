<?php

namespace App\Http\\Controllers\\Api\Security;

use App\Http\Controllers\Controller;
use App\Services\Security\APIKeyService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class APIKeyController extends Controller
{
    public function __construct(
        private APIKeyService $apiKeyService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $keys = $this->apiKeyService->getUserKeys($request->user());

        return response()->json(['data' => $keys]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'nullable|array',
            'scopes.*' => 'string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $expiresAt = $request->expires_in_days
            ? Carbon::now()->addDays($request->expires_in_days)
            : null;

        $keyData = $this->apiKeyService->generateKey(
            $request->user(),
            $request->name,
            $request->scopes ?? [],
            $expiresAt
        );

        return response()->json([
            'message' => 'API key created successfully',
            'data' => $keyData,
        ], 201);
    }

    public function destroy(Request $request, int $keyId): JsonResponse
    {
        \App\Models\ApiKey::where('id', $keyId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->apiKeyService->revokeKey($keyId);

        return response()->json(['message' => 'API key revoked successfully']);
    }

    public function rotate(Request $request, int $keyId): JsonResponse
    {
        \App\Models\ApiKey::where('id', $keyId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $newKeyData = $this->apiKeyService->rotateKey($keyId);

        return response()->json([
            'message' => 'API key rotated successfully',
            'data' => $newKeyData,
        ]);
    }
}

