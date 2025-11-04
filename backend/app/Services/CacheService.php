<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class CacheService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('cache-strategy');
    }

    /**
     * Cache API response with automatic invalidation
     */
    public function cacheApiResponse(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (! $this->config['strategies']['api_responses']['enabled']) {
            return $callback();
        }

        $ttl = $ttl ?? $this->config['strategies']['api_responses']['ttl'];
        $fullKey = "api:response:{$key}";

        return Cache::tags($this->config['strategies']['api_responses']['tags'])
            ->remember($fullKey, $ttl, $callback);
    }

    /**
     * Cache database query results
     */
    public function cacheQuery(string $key, callable $callback, ?int $ttl = null): mixed
    {
        if (! $this->config['strategies']['database_queries']['enabled']) {
            return $callback();
        }

        $ttl = $ttl ?? $this->config['strategies']['database_queries']['ttl'];
        $fullKey = "query:{$key}";

        return Cache::tags($this->config['strategies']['database_queries']['tags'])
            ->remember($fullKey, $ttl, $callback);
    }

    /**
     * Cache page content
     */
    public function cachePage(string $url, string $content, ?int $ttl = null): void
    {
        if (! $this->config['strategies']['page_cache']['enabled']) {
            return;
        }

        $ttl = $ttl ?? $this->config['strategies']['page_cache']['ttl'];
        $key = 'page:'.md5($url);

        Cache::tags($this->config['strategies']['page_cache']['tags'])
            ->put($key, $content, $ttl);
    }

    /**
     * Get cached page
     */
    public function getCachedPage(string $url): ?string
    {
        if (! $this->config['strategies']['page_cache']['enabled']) {
            return null;
        }

        $key = 'page:'.md5($url);

        return Cache::tags($this->config['strategies']['page_cache']['tags'])
            ->get($key);
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateByTags(array $tags): void
    {
        Cache::tags($tags)->flush();
    }

    /**
     * Invalidate cache for a model
     */
    public function invalidateModel(Model $model): void
    {
        $modelClass = class_basename($model);
        $tags = $this->config['invalidation']['models'][$modelClass] ?? [];

        if (! empty($tags)) {
            $this->invalidateByTags($tags);
        }

        // Also invalidate specific model cache
        Cache::forget("model:{$modelClass}:{$model->id}");
    }

    /**
     * Warm up cache with popular data
     */
    public function warmCache(): void
    {
        if (! $this->config['warming']['enabled']) {
            return;
        }

        foreach ($this->config['warming']['routes'] as $route) {
            try {
                $response = app('router')->dispatch(
                    \Illuminate\Http\Request::create($route, 'GET')
                );

                if ($response->isSuccessful()) {
                    $this->cachePage($route, $response->getContent());
                }
            } catch (\Exception $e) {
                \Log::warning("Failed to warm cache for route: {$route}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        try {
            $redis = Redis::connection();

            return [
                'hits' => $redis->get('cache:hits') ?? 0,
                'misses' => $redis->get('cache:misses') ?? 0,
                'size' => $redis->dbsize(),
                'memory_usage' => $redis->info('memory')['used_memory_human'] ?? 'N/A',
                'hit_rate' => $this->calculateHitRate(),
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Unable to retrieve cache statistics',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate cache hit rate
     */
    protected function calculateHitRate(): float
    {
        $hits = (int) (Redis::get('cache:hits') ?? 0);
        $misses = (int) (Redis::get('cache:misses') ?? 0);
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }

    /**
     * Record cache hit
     */
    public function recordHit(): void
    {
        Redis::incr('cache:hits');
    }

    /**
     * Record cache miss
     */
    public function recordMiss(): void
    {
        Redis::incr('cache:misses');
    }

    /**
     * Clear all cache
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to clear cache', ['error' => $e->getMessage()]);

            return false;
        }
    }

    /**
     * Get/Set with cache-aside pattern
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = []): mixed
    {
        $cache = empty($tags) ? Cache::store() : Cache::tags($tags);

        return $cache->remember($key, $ttl, function () use ($callback) {
            $this->recordMiss();

            return $callback();
        });
    }
}
