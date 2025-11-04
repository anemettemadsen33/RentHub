<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CachingService
{
    private string $prefix = 'renthub:';

    /**
     * Cache query result
     */
    public function cacheQuery(string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::remember($this->prefix.$key, $ttl, $callback);
    }

    /**
     * Cache paginated query
     */
    public function cachePaginatedQuery(string $key, int $page, int $perPage, \Closure $callback, int $ttl = 3600)
    {
        $cacheKey = "{$this->prefix}{$key}:page:{$page}:per_page:{$perPage}";

        return Cache::remember($cacheKey, $ttl, $callback);
    }

    /**
     * Cache model
     */
    public function cacheModel(string $modelClass, int $id, int $ttl = 3600)
    {
        $key = $this->prefix.class_basename($modelClass).":{$id}";

        return Cache::remember($key, $ttl, function () use ($modelClass, $id) {
            return $modelClass::find($id);
        });
    }

    /**
     * Cache collection
     */
    public function cacheCollection(string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::remember($this->prefix.$key, $ttl, $callback);
    }

    /**
     * Cache API response
     */
    public function cacheAPIResponse(string $endpoint, array $params, \Closure $callback, int $ttl = 300)
    {
        $key = $this->prefix.'api:'.$endpoint.':'.md5(json_encode($params));

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Cache fragment
     */
    public function cacheFragment(string $key, \Closure $callback, int $ttl = 3600): string
    {
        return Cache::remember($this->prefix.'fragment:'.$key, $ttl, $callback);
    }

    /**
     * Cache page
     */
    public function cachePage(string $url, \Closure $callback, int $ttl = 3600): string
    {
        $key = $this->prefix.'page:'.md5($url);

        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Invalidate cache
     */
    public function invalidate(string $key): bool
    {
        return Cache::forget($this->prefix.$key);
    }

    /**
     * Invalidate pattern
     */
    public function invalidatePattern(string $pattern): int
    {
        $keys = $this->getKeysByPattern($this->prefix.$pattern);

        $count = 0;
        foreach ($keys as $key) {
            if (Cache::forget($key)) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Invalidate model cache
     */
    public function invalidateModel(string $modelClass, int $id): bool
    {
        $key = $this->prefix.class_basename($modelClass).":{$id}";

        return Cache::forget($key);
    }

    /**
     * Invalidate collection cache
     */
    public function invalidateCollection(string $key): bool
    {
        return Cache::forget($this->prefix.$key);
    }

    /**
     * Get keys by pattern
     */
    private function getKeysByPattern(string $pattern): array
    {
        try {
            return Redis::keys($pattern.'*');
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Cache tags
     */
    public function cacheWithTags(array $tags, string $key, \Closure $callback, int $ttl = 3600)
    {
        return Cache::tags($tags)->remember($this->prefix.$key, $ttl, $callback);
    }

    /**
     * Flush tags
     */
    public function flushTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Increment counter
     */
    public function increment(string $key, int $value = 1): int
    {
        return Cache::increment($this->prefix.$key, $value);
    }

    /**
     * Decrement counter
     */
    public function decrement(string $key, int $value = 1): int
    {
        return Cache::decrement($this->prefix.$key, $value);
    }

    /**
     * Get or set cache
     */
    public function getOrSet(string $key, $value, int $ttl = 3600)
    {
        $fullKey = $this->prefix.$key;

        if (Cache::has($fullKey)) {
            return Cache::get($fullKey);
        }

        $resolvedValue = $value instanceof \Closure ? $value() : $value;
        Cache::put($fullKey, $resolvedValue, $ttl);

        return $resolvedValue;
    }

    /**
     * Cache forever
     */
    public function forever(string $key, $value): bool
    {
        return Cache::forever($this->prefix.$key, $value);
    }

    /**
     * Pull (get and delete)
     */
    public function pull(string $key)
    {
        return Cache::pull($this->prefix.$key);
    }

    /**
     * Add (only if doesn't exist)
     */
    public function add(string $key, $value, int $ttl = 3600): bool
    {
        return Cache::add($this->prefix.$key, $value, $ttl);
    }

    /**
     * Warm cache
     */
    public function warmCache(array $warmers): void
    {
        foreach ($warmers as $key => $callback) {
            if (! Cache::has($this->prefix.$key)) {
                Cache::put($this->prefix.$key, $callback(), 3600);
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $info = Redis::info();

            return [
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
                'memory_used' => $info['used_memory_human'] ?? '0',
                'keys' => $info['db0']['keys'] ?? 0,
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Calculate hit rate
     */
    private function calculateHitRate(array $info): float
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
    }
}
