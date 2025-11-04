<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    protected array $tags = [];

    protected int $ttl = 3600;

    /**
     * Cache with tags for easy invalidation
     */
    public function remember(string $key, callable $callback, ?int $ttl = null, array $tags = [])
    {
        $ttl = $ttl ?? $this->ttl;

        if (! empty($tags)) {
            return Cache::tags($tags)->remember($key, $ttl, $callback);
        }

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache properties with related data
     */
    public function cacheProperty(int $propertyId, callable $callback)
    {
        return $this->remember(
            "property:{$propertyId}",
            $callback,
            3600,
            ['properties', "property:{$propertyId}"]
        );
    }

    /**
     * Cache search results
     */
    public function cacheSearch(array $params, callable $callback)
    {
        $key = 'search:'.md5(json_encode($params));

        return $this->remember(
            $key,
            $callback,
            1800, // 30 minutes
            ['searches']
        );
    }

    /**
     * Cache user data
     */
    public function cacheUser(int $userId, callable $callback)
    {
        return $this->remember(
            "user:{$userId}",
            $callback,
            1800,
            ['users', "user:{$userId}"]
        );
    }

    /**
     * Invalidate property cache
     */
    public function invalidateProperty(int $propertyId): void
    {
        Cache::tags(["property:{$propertyId}"])->flush();
        Cache::tags(['properties'])->flush();
        Cache::tags(['searches'])->flush();
    }

    /**
     * Invalidate user cache
     */
    public function invalidateUser(int $userId): void
    {
        Cache::tags(["user:{$userId}"])->flush();
        Cache::tags(['users'])->flush();
    }

    /**
     * Warm up cache for popular items
     */
    public function warmUpPopularProperties(): void
    {
        $popularPropertyIds = Redis::zrevrange('popular_properties', 0, 99);

        foreach ($popularPropertyIds as $propertyId) {
            $this->cacheProperty($propertyId, function () use ($propertyId) {
                return \App\Models\Property::with([
                    'user',
                    'amenities',
                    'images',
                    'location',
                ])->find($propertyId);
            });
        }
    }

    /**
     * Cache API responses
     */
    public function cacheApiResponse(string $endpoint, array $params, callable $callback)
    {
        $key = 'api:'.md5($endpoint.json_encode($params));

        return $this->remember($key, $callback, 300, ['api-responses']);
    }

    /**
     * Store fragment cache (for partial views)
     */
    public function cacheFragment(string $name, callable $callback, int $ttl = 3600)
    {
        return $this->remember("fragment:{$name}", $callback, $ttl, ['fragments']);
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        return [
            'driver' => config('cache.default'),
            'hits' => $this->getCacheHits(),
            'misses' => $this->getCacheMisses(),
            'memory_usage' => $this->getMemoryUsage(),
        ];
    }

    protected function getCacheHits(): int
    {
        return (int) Redis::get('cache:stats:hits') ?? 0;
    }

    protected function getCacheMisses(): int
    {
        return (int) Redis::get('cache:stats:misses') ?? 0;
    }

    protected function getMemoryUsage(): string
    {
        $info = Redis::info('memory');

        return $info['used_memory_human'] ?? 'N/A';
    }
}
