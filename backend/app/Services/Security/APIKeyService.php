<?php

namespace App\Services\Security;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class APIKeyService
{
    /**
     * Generate new API key
     */
    public function generateKey(
        User $user,
        string $name,
        array $scopes = [],
        ?Carbon $expiresAt = null
    ): array {
        $key = 'rh_' . Str::random(32);
        $hashedKey = Hash::make($key);

        $apiKey = ApiKey::create([
            'user_id' => $user->id,
            'name' => $name,
            'key' => $hashedKey,
            'scopes' => $scopes,
            'expires_at' => $expiresAt,
            'last_used_at' => null,
        ]);

        return [
            'id' => $apiKey->id,
            'key' => $key, // Return plain key only once
            'name' => $name,
            'scopes' => $scopes,
            'created_at' => $apiKey->created_at,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Validate API key
     */
    public function validateKey(string $key): ?ApiKey
    {
        $apiKeys = ApiKey::where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->get();

        foreach ($apiKeys as $apiKey) {
            if (Hash::check($key, $apiKey->key)) {
                // Update last used timestamp
                $apiKey->update([
                    'last_used_at' => now(),
                    'usage_count' => $apiKey->usage_count + 1,
                ]);

                return $apiKey;
            }
        }

        return null;
    }

    /**
     * Revoke API key
     */
    public function revokeKey(int $keyId): bool
    {
        return ApiKey::where('id', $keyId)->update(['is_active' => false]);
    }

    /**
     * Rotate API key
     */
    public function rotateKey(int $oldKeyId): array
    {
        $oldKey = ApiKey::findOrFail($oldKeyId);
        
        // Generate new key
        $newKeyData = $this->generateKey(
            $oldKey->user,
            $oldKey->name,
            $oldKey->scopes,
            $oldKey->expires_at
        );

        // Revoke old key
        $this->revokeKey($oldKeyId);

        return $newKeyData;
    }

    /**
     * Check if key has scope
     */
    public function hasScope(ApiKey $apiKey, string $scope): bool
    {
        if (empty($apiKey->scopes)) {
            return true; // Full access if no scopes defined
        }

        return in_array($scope, $apiKey->scopes) || in_array('*', $apiKey->scopes);
    }

    /**
     * Get user's API keys
     */
    public function getUserKeys(User $user): array
    {
        return ApiKey::where('user_id', $user->id)
            ->where('is_active', true)
            ->select(['id', 'name', 'scopes', 'created_at', 'last_used_at', 'expires_at', 'usage_count'])
            ->get()
            ->toArray();
    }

    /**
     * Clean expired keys
     */
    public function cleanExpiredKeys(): int
    {
        return ApiKey::where('expires_at', '<', now())->delete();
    }
}
