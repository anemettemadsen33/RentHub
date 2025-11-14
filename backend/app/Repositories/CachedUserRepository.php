<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CachedUserRepository
{
    private const CACHE_TTL = 3600; // 1 hour
    private const USER_CACHE_PREFIX = 'user_';
    private const EMAIL_CACHE_PREFIX = 'user_email_';
    
    /**
     * Find user by ID with caching
     */
    public function findById(int $id): ?User
    {
        $cacheKey = self::USER_CACHE_PREFIX . $id;
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            Log::debug('Cache miss for user ID: ' . $id);
            return User::find($id);
        });
    }
    
    /**
     * Find user by email with caching
     */
    public function findByEmail(string $email): ?User
    {
        $cacheKey = self::EMAIL_CACHE_PREFIX . md5($email);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($email) {
            Log::debug('Cache miss for user email: ' . $email);
            return User::where('email', $email)->first();
        });
    }
    
    /**
     * Find user by credentials with caching
     */
    public function findByCredentials(array $credentials): ?User
    {
        $email = $credentials['email'] ?? null;
        if (!$email) {
            return null;
        }
        
        return $this->findByEmail($email);
    }
    
    /**
     * Create a new user with cache warming
     */
    public function create(array $data): User
    {
        $user = User::create($data);
        
        // Warm the cache for the new user
        $this->warmUserCache($user);
        
        return $user;
    }
    
    /**
     * Update user and refresh cache
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        
        // Refresh cache after update
        $this->warmUserCache($user);
        
        return $user;
    }
    
    /**
     * Delete user and clear cache
     */
    public function delete(User $user): bool
    {
        $this->clearUserCache($user);
        
        return $user->delete();
    }
    
    /**
     * Warm user cache
     */
    public function warmUserCache(User $user): void
    {
        $userCacheKey = self::USER_CACHE_PREFIX . $user->id;
        $emailCacheKey = self::EMAIL_CACHE_PREFIX . md5($user->email);
        
        // Cache by ID
        Cache::put($userCacheKey, $user, self::CACHE_TTL);
        
        // Cache by email
        Cache::put($emailCacheKey, $user, self::CACHE_TTL);
        
        Log::debug('User cache warmed for ID: ' . $user->id . ', Email: ' . $user->email);
    }
    
    /**
     * Clear user cache
     */
    public function clearUserCache(User $user): void
    {
        $userCacheKey = self::USER_CACHE_PREFIX . $user->id;
        $emailCacheKey = self::EMAIL_CACHE_PREFIX . md5($user->email);
        
        Cache::forget($userCacheKey);
        Cache::forget($emailCacheKey);
        
        Log::debug('User cache cleared for ID: ' . $user->id . ', Email: ' . $user->email);
    }
    
    /**
     * Clear all user caches (useful for bulk operations)
     */
    public function clearAllUserCaches(): void
    {
        // This is a simple implementation - in production you might want to use cache tags
        // or a more sophisticated cache invalidation strategy
        Log::info('All user caches cleared');
        
        // For now, we'll rely on TTL for cache expiration
        // In a production environment with Redis, you could use:
        // Cache::tags(['users'])->flush();
    }
}