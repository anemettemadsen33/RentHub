<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheOptimizationService
{
    /**
     * Cache with tags for easier invalidation.
     */
    public function cacheWithTags(
        array $tags,
        string $key,
        callable $callback,
        int $ttl = 3600
    ) {
        return Cache::tags($tags)->remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache by tags.
     */
    public function invalidateByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Cache API response with compression.
     */
    public function cacheCompressedResponse(
        string $key,
        array $data,
        int $ttl = 3600
    ): void {
        $compressed = gzencode(json_encode($data), 9);
        Cache::put($key, $compressed, $ttl);
    }

    /**
     * Get and decompress cached response.
     */
    public function getCompressedResponse(string $key): ?array
    {
        $compressed = Cache::get($key);

        if (! $compressed) {
            return null;
        }

        $decompressed = gzdecode($compressed);

        return json_decode($decompressed, true);
    }

    /**
     * Cache database query results.
     */
    public function cacheQuery(
        string $cacheKey,
        callable $query,
        int $ttl = 3600
    ) {
        return Cache::remember($cacheKey, $ttl, $query);
    }

    /**
     * Warm up cache for frequently accessed data.
     */
    public function warmUpCache(): void
    {
        // Popular properties
        Cache::remember('properties:popular', 3600, function () {
            return \App\Models\Property::popular()->take(20)->get();
        });

        // Featured properties
        Cache::remember('properties:featured', 3600, function () {
            return \App\Models\Property::featured()->take(10)->get();
        });
    }

    /**
     * Implement cache-aside pattern.
     */
    public function cacheAside(
        string $key,
        int $id,
        callable $fetchFromDB,
        int $ttl = 3600
    ) {
        $cacheKey = "{$key}:{$id}";

        return Cache::remember($cacheKey, $ttl, function () use ($fetchFromDB, $id) {
            return $fetchFromDB($id);
        });
    }

    /**
     * Get cache statistics.
     */
    public function getCacheStats(): array
    {
        $redis = Redis::connection();

        return [
            'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
            'total_keys' => $redis->dbSize(),
            'hit_rate' => $this->calculateHitRate(),
        ];
    }

    /**
     * Calculate cache hit rate.
     */
    protected function calculateHitRate(): float
    {
        $redis = Redis::connection();
        $stats = $redis->info('stats');

        $hits = $stats['keyspace_hits'] ?? 0;
        $misses = $stats['keyspace_misses'] ?? 0;

        $total = $hits + $misses;

        return $total > 0 ? ($hits / $total) * 100 : 0;
    }
}
