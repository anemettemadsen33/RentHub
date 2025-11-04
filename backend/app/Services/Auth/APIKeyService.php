<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\ApiKey;
use Illuminate\Support\Str;
use Carbon\Carbon;

class APIKeyService
{
    /**
     * Create new API key for user
     */
    public function createKey(
        User $user,
        string $name,
        ?array $permissions = null,
        ?Carbon $expiresAt = null,
        ?string $ipWhitelist = null
    ): ApiKey {
        $key = 'rh_' . Str::random(40);
        
        return ApiKey::create([
            'user_id' => $user->id,
            'name' => $name,
            'key' => hash('sha256', $key),
            'plain_key' => $key, // Only available at creation
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'ip_whitelist' => $ipWhitelist,
            'last_used_at' => null,
        ]);
    }

    /**
     * Validate API key
     */
    public function validateKey(string $key): ?ApiKey
    {
        $hashedKey = hash('sha256', $key);
        
        $apiKey = ApiKey::where('key', $hashedKey)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($apiKey) {
            // Update last used
            $apiKey->update([
                'last_used_at' => now(),
                'usage_count' => $apiKey->usage_count + 1,
            ]);
        }

        return $apiKey;
    }

    /**
     * Check if IP is whitelisted
     */
    public function isIpAllowed(ApiKey $apiKey, string $ip): bool
    {
        if (!$apiKey->ip_whitelist) {
            return true;
        }

        $whitelist = explode(',', $apiKey->ip_whitelist);
        return in_array($ip, array_map('trim', $whitelist));
    }

    /**
     * Check if API key has permission
     */
    public function hasPermission(ApiKey $apiKey, string $permission): bool
    {
        if (!$apiKey->permissions) {
            return true; // No restrictions
        }

        return in_array($permission, $apiKey->permissions);
    }

    /**
     * Revoke API key
     */
    public function revokeKey(ApiKey $apiKey): bool
    {
        return $apiKey->update(['active' => false]);
    }

    /**
     * Rotate API key
     */
    public function rotateKey(ApiKey $apiKey): ApiKey
    {
        // Revoke old key
        $this->revokeKey($apiKey);

        // Create new key with same settings
        return $this->createKey(
            $apiKey->user,
            $apiKey->name . ' (rotated)',
            $apiKey->permissions,
            $apiKey->expires_at,
            $apiKey->ip_whitelist
        );
    }

    /**
     * Get user API keys
     */
    public function getUserKeys(User $user)
    {
        return ApiKey::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Clean up expired keys
     */
    public function cleanupExpiredKeys(): int
    {
        return ApiKey::where('expires_at', '<', now())
            ->delete();
    }

    /**
     * Clean up unused keys (not used in last 90 days)
     */
    public function cleanupUnusedKeys(int $days = 90): int
    {
        return ApiKey::where('last_used_at', '<', now()->subDays($days))
            ->orWhereNull('last_used_at')
            ->where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
