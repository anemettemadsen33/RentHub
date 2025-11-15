<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Cache;

class CacheHelper
{
    /**
     * Check if current cache driver supports tags
     */
    public static function supportsTags(): bool
    {
        $driver = config('cache.default');
        $supportedDrivers = ['redis', 'memcached', 'dynamodb'];
        
        return in_array($driver, $supportedDrivers);
    }

    /**
     * Get cache with tags support check
     */
    public static function tags(array $tags)
    {
        if (self::supportsTags()) {
            return Cache::tags($tags);
        }
        
        return Cache::store();
    }

    /**
     * Remember with tags support check
     */
    public static function remember(array $tags, string $key, $ttl, callable $callback)
    {
        if (self::supportsTags()) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }
        
        // Use prefixed key when tags are not supported
        $prefixedKey = implode('_', $tags) . '_' . $key;
        return Cache::remember($prefixedKey, $ttl, $callback);
    }

    /**
     * Flush cache tags with support check
     */
    public static function flush(array $tags): void
    {
        if (self::supportsTags()) {
            Cache::tags($tags)->flush();
        } else {
            // When tags are not supported, just clear the entire cache
            // Or implement a prefix-based clearing strategy
            Cache::flush();
        }
    }
}
