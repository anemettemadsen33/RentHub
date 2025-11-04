<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheStrategyService
{
    /**
     * Application cache with tags
     */
    public function appCache(string $key, $value, int $ttl = 3600, array $tags = [])
    {
        if (!empty($tags)) {
            return Cache::tags($tags)->put($key, $value, $ttl);
        }
        
        return Cache::put($key, $value, $ttl);
    }

    /**
     * Get from application cache
     */
    public function getAppCache(string $key, array $tags = [])
    {
        if (!empty($tags)) {
            return Cache::tags($tags)->get($key);
        }
        
        return Cache::get($key);
    }

    /**
     * Query cache for database results
     */
    public function queryCache(string $key, callable $callback, int $ttl = 600)
    {
        return Cache::remember("query:{$key}", $ttl, $callback);
    }

    /**
     * Page cache for full HTML responses
     */
    public function pageCache(string $key, string $content, int $ttl = 1800): void
    {
        Cache::put("page:{$key}", $content, $ttl);
    }

    /**
     * Fragment cache for partial views
     */
    public function fragmentCache(string $key, callable $callback, int $ttl = 900)
    {
        return Cache::remember("fragment:{$key}", $ttl, $callback);
    }

    /**
     * Cache invalidation by tag
     */
    public function invalidateByTag(string $tag): void
    {
        Cache::tags([$tag])->flush();
    }

    /**
     * Cache invalidation by pattern
     */
    public function invalidateByPattern(string $pattern): void
    {
        $keys = $this->getKeysByPattern($pattern);
        
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Get cache keys by pattern (Redis only)
     */
    protected function getKeysByPattern(string $pattern): array
    {
        try {
            return Redis::keys($pattern);
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Warm up cache with frequently accessed data
     */
    public function warmUpCache(array $items): void
    {
        foreach ($items as $key => $callback) {
            if (!Cache::has($key)) {
                $value = is_callable($callback) ? $callback() : $callback;
                Cache::put($key, $value, 3600);
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): array
    {
        try {
            $redis = Redis::connection();
            $info = $redis->info();
            
            return [
                'memory_used' => $info['used_memory_human'] ?? 'N/A',
                'total_keys' => $redis->dbSize(),
                'hit_rate' => $this->calculateHitRate($info),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }

    /**
     * Browser cache headers
     */
    public function browserCacheHeaders(int $maxAge = 86400): array
    {
        return [
            'Cache-Control' => "public, max-age={$maxAge}",
            'Expires' => gmdate('D, d M Y H:i:s', time() + $maxAge) . ' GMT',
            'Pragma' => 'public',
        ];
    }
}
