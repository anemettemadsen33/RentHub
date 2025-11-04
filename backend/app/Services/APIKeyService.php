<?php

namespace App\Services;

use App\Models\User;
use App\Models\APIKey;
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
        array $permissions = [],
        ?Carbon $expiresAt = null,
        array $ipWhitelist = [],
        int $rateLimit = 1000
    ): array {
        $key = 'rh_' . Str::random(32);
        $hashedKey = hash('sha256', $key);
        
        $apiKey = APIKey::create([
            'user_id' => $user->id,
            'name' => $name,
            'key_hash' => $hashedKey,
            'permissions' => $permissions,
            'expires_at' => $expiresAt,
            'ip_whitelist' => $ipWhitelist,
            'rate_limit' => $rateLimit,
            'is_active' => true,
            'last_used_at' => null,
        ]);
        
        return [
            'id' => $apiKey->id,
            'key' => $key, // Only returned once
            'name' => $name,
            'created_at' => $apiKey->created_at,
            'expires_at' => $expiresAt,
        ];
    }
    
    /**
     * Validate API key
     */
    public function validateKey(string $key, ?string $ip = null): ?APIKey
    {
        $hashedKey = hash('sha256', $key);
        
        $apiKey = APIKey::where('key_hash', $hashedKey)
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
            
        if (!$apiKey) {
            return null;
        }
        
        // Check IP whitelist
        if (!empty($apiKey->ip_whitelist) && $ip) {
            if (!in_array($ip, $apiKey->ip_whitelist)) {
                \Log::warning("API key access from unauthorized IP", [
                    'key_id' => $apiKey->id,
                    'ip' => $ip,
                ]);
                return null;
            }
        }
        
        // Update last used
        $apiKey->update([
            'last_used_at' => now(),
            'usage_count' => $apiKey->usage_count + 1,
        ]);
        
        return $apiKey;
    }
    
    /**
     * Check rate limit
     */
    public function checkRateLimit(APIKey $apiKey): bool
    {
        $cacheKey = "api_key:{$apiKey->id}:rate_limit";
        $count = cache()->get($cacheKey, 0);
        
        if ($count >= $apiKey->rate_limit) {
            return false;
        }
        
        cache()->put($cacheKey, $count + 1, now()->addHour());
        
        return true;
    }
    
    /**
     * Check permission
     */
    public function hasPermission(APIKey $apiKey, string $permission): bool
    {
        if (empty($apiKey->permissions)) {
            return true; // No restrictions
        }
        
        return in_array($permission, $apiKey->permissions) || in_array('*', $apiKey->permissions);
    }
    
    /**
     * Revoke API key
     */
    public function revokeKey(int $keyId): bool
    {
        return APIKey::where('id', $keyId)
            ->update(['is_active' => false]) > 0;
    }
    
    /**
     * Rotate API key
     */
    public function rotateKey(APIKey $oldKey): array
    {
        $oldKey->update(['is_active' => false]);
        
        return $this->generateKey(
            $oldKey->user,
            $oldKey->name,
            $oldKey->permissions ?? [],
            $oldKey->expires_at,
            $oldKey->ip_whitelist ?? [],
            $oldKey->rate_limit
        );
    }
    
    /**
     * Get user API keys
     */
    public function getUserKeys(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return APIKey::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Clean expired keys
     */
    public function cleanExpiredKeys(): int
    {
        return APIKey::where('expires_at', '<', now())
            ->where('is_active', true)
            ->update(['is_active' => false]);
    }
    
    /**
     * Get key usage statistics
     */
    public function getKeyStats(APIKey $apiKey): array
    {
        return [
            'total_requests' => $apiKey->usage_count,
            'last_used' => $apiKey->last_used_at,
            'created_at' => $apiKey->created_at,
            'expires_at' => $apiKey->expires_at,
            'is_active' => $apiKey->is_active,
            'rate_limit' => $apiKey->rate_limit,
            'current_hour_usage' => cache()->get("api_key:{$apiKey->id}:rate_limit", 0),
        ];
    }
}
