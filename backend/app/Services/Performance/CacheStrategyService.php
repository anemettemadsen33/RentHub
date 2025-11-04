<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheStrategyService
{
    private const DEFAULT_TTL = 3600; // 1 hour

    private const SHORT_TTL = 300;    // 5 minutes

    private const LONG_TTL = 86400;   // 24 hours

    /**
     * Application-level cache with Redis
     */
    public function cacheApplication(string $key, $value, int $ttl = self::DEFAULT_TTL): void
    {
        Cache::tags(['application'])->put($key, $value, $ttl);

        Log::info('Application cache set', [
            'key' => $key,
            'ttl' => $ttl,
        ]);
    }

    /**
     * Get from application cache
     */
    public function getApplicationCache(string $key)
    {
        return Cache::tags(['application'])->get($key);
    }

    /**
     * Database query cache
     */
    public function cacheQuery(string $query, array $bindings, $result, int $ttl = self::SHORT_TTL): void
    {
        $key = $this->generateQueryKey($query, $bindings);
        Cache::tags(['queries'])->put($key, $result, $ttl);
    }

    /**
     * Get cached query result
     */
    public function getCachedQuery(string $query, array $bindings)
    {
        $key = $this->generateQueryKey($query, $bindings);

        return Cache::tags(['queries'])->get($key);
    }

    /**
     * Page cache
     */
    public function cachePage(string $url, string $content, int $ttl = self::DEFAULT_TTL): void
    {
        $key = 'page:'.md5($url);
        Cache::tags(['pages'])->put($key, [
            'content' => $content,
            'url' => $url,
            'cached_at' => now(),
            'headers' => $this->getResponseHeaders(),
        ], $ttl);
    }

    /**
     * Get cached page
     */
    public function getCachedPage(string $url): ?array
    {
        $key = 'page:'.md5($url);

        return Cache::tags(['pages'])->get($key);
    }

    /**
     * Fragment cache for partial views
     */
    public function cacheFragment(string $name, string $content, int $ttl = self::DEFAULT_TTL): void
    {
        $key = 'fragment:'.$name;
        Cache::tags(['fragments'])->put($key, $content, $ttl);
    }

    /**
     * Get cached fragment
     */
    public function getCachedFragment(string $name): ?string
    {
        $key = 'fragment:'.$name;

        return Cache::tags(['fragments'])->get($key);
    }

    /**
     * API response cache
     */
    public function cacheApiResponse(string $endpoint, array $params, $response, int $ttl = self::SHORT_TTL): void
    {
        $key = $this->generateApiCacheKey($endpoint, $params);

        Cache::tags(['api'])->put($key, [
            'response' => $response,
            'cached_at' => now(),
            'ttl' => $ttl,
        ], $ttl);
    }

    /**
     * Get cached API response
     */
    public function getCachedApiResponse(string $endpoint, array $params): ?array
    {
        $key = $this->generateApiCacheKey($endpoint, $params);

        return Cache::tags(['api'])->get($key);
    }

    /**
     * Cache with CDN headers
     */
    public function setCdnCache(string $content, int $maxAge = 3600): array
    {
        return [
            'content' => $content,
            'headers' => [
                'Cache-Control' => "public, max-age={$maxAge}",
                'Expires' => gmdate('D, d M Y H:i:s', time() + $maxAge).' GMT',
                'ETag' => md5($content),
                'Last-Modified' => gmdate('D, d M Y H:i:s').' GMT',
            ],
        ];
    }

    /**
     * Browser cache headers
     */
    public function getBrowserCacheHeaders(string $type = 'default'): array
    {
        $configs = [
            'static' => [
                'Cache-Control' => 'public, max-age=31536000, immutable',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 31536000).' GMT',
            ],
            'dynamic' => [
                'Cache-Control' => 'public, max-age=3600, must-revalidate',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 3600).' GMT',
            ],
            'private' => [
                'Cache-Control' => 'private, max-age=600',
                'Expires' => gmdate('D, d M Y H:i:s', time() + 600).' GMT',
            ],
            'no-cache' => [
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ],
        ];

        return $configs[$type] ?? $configs['default'];
    }

    /**
     * Implement cache warming
     */
    public function warmCache(array $keys): array
    {
        $results = [];

        foreach ($keys as $key => $callable) {
            try {
                $value = is_callable($callable) ? $callable() : $callable;
                Cache::tags(['warmed'])->put($key, $value, self::LONG_TTL);
                $results[$key] = 'success';
            } catch (\Exception $e) {
                $results[$key] = 'failed: '.$e->getMessage();
                Log::error('Cache warming failed', [
                    'key' => $key,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Cache invalidation strategies
     */
    public function invalidateCache(string $pattern = '*'): int
    {
        if ($pattern === '*') {
            Cache::flush();

            return -1;
        }

        $count = 0;
        $keys = $this->getKeysByPattern($pattern);

        foreach ($keys as $key) {
            Cache::forget($key);
            $count++;
        }

        return $count;
    }

    /**
     * Invalidate cache by tags
     */
    public function invalidateCacheByTags(array $tags): void
    {
        Cache::tags($tags)->flush();

        Log::info('Cache invalidated by tags', ['tags' => $tags]);
    }

    /**
     * Cache stampede protection
     */
    public function cacheWithLock(string $key, callable $callback, int $ttl = self::DEFAULT_TTL, int $lockSeconds = 10)
    {
        // Check if value exists in cache
        $value = Cache::get($key);
        if ($value !== null) {
            return $value;
        }

        // Try to acquire lock
        $lock = Cache::lock("lock:{$key}", $lockSeconds);

        try {
            if ($lock->get()) {
                // Double-check cache after acquiring lock
                $value = Cache::get($key);
                if ($value !== null) {
                    return $value;
                }

                // Generate new value
                $value = $callback();

                // Store in cache
                Cache::put($key, $value, $ttl);

                return $value;
            } else {
                // Couldn't acquire lock, wait and retry
                sleep(1);

                return $this->cacheWithLock($key, $callback, $ttl, $lockSeconds);
            }
        } finally {
            $lock->release();
        }
    }

    /**
     * Multilevel cache strategy
     */
    public function getMultilevelCache(string $key, callable $callback, array $levels = ['memory', 'redis', 'database'])
    {
        foreach ($levels as $level) {
            $value = $this->getFromLevel($level, $key);

            if ($value !== null) {
                // Backfill upper levels
                $this->backfillLevels($levels, $level, $key, $value);

                return $value;
            }
        }

        // Not found in any level, generate and cache
        $value = $callback();
        $this->cacheToAllLevels($levels, $key, $value);

        return $value;
    }

    /**
     * Cache statistics
     */
    public function getCacheStatistics(): array
    {
        $redis = Redis::connection();

        return [
            'info' => $redis->info('stats'),
            'memory' => $redis->info('memory'),
            'keyspace' => $redis->info('keyspace'),
            'hits' => $redis->info('stats')['keyspace_hits'] ?? 0,
            'misses' => $redis->info('stats')['keyspace_misses'] ?? 0,
            'hit_rate' => $this->calculateHitRate(),
        ];
    }

    /**
     * Generate query cache key
     */
    private function generateQueryKey(string $query, array $bindings): string
    {
        return 'query:'.md5($query.serialize($bindings));
    }

    /**
     * Generate API cache key
     */
    private function generateApiCacheKey(string $endpoint, array $params): string
    {
        ksort($params);

        return 'api:'.md5($endpoint.serialize($params));
    }

    /**
     * Get response headers for caching
     */
    private function getResponseHeaders(): array
    {
        return [
            'Content-Type' => 'text/html; charset=utf-8',
            'X-Cache' => 'HIT',
            'X-Cache-Time' => now()->toIso8601String(),
        ];
    }

    /**
     * Get keys by pattern
     */
    private function getKeysByPattern(string $pattern): array
    {
        $redis = Redis::connection();

        return $redis->keys($pattern);
    }

    /**
     * Get value from specific cache level
     */
    private function getFromLevel(string $level, string $key)
    {
        switch ($level) {
            case 'memory':
                return apcu_fetch($key) ?: null;
            case 'redis':
                return Cache::get($key);
            case 'database':
                return \DB::table('cache')->where('key', $key)->value('value');
            default:
                return null;
        }
    }

    /**
     * Backfill upper cache levels
     */
    private function backfillLevels(array $levels, string $currentLevel, string $key, $value): void
    {
        $index = array_search($currentLevel, $levels);

        for ($i = 0; $i < $index; $i++) {
            $this->cacheToLevel($levels[$i], $key, $value);
        }
    }

    /**
     * Cache to all levels
     */
    private function cacheToAllLevels(array $levels, string $key, $value): void
    {
        foreach ($levels as $level) {
            $this->cacheToLevel($level, $key, $value);
        }
    }

    /**
     * Cache to specific level
     */
    private function cacheToLevel(string $level, string $key, $value): void
    {
        switch ($level) {
            case 'memory':
                apcu_store($key, $value, self::SHORT_TTL);
                break;
            case 'redis':
                Cache::put($key, $value, self::DEFAULT_TTL);
                break;
            case 'database':
                \DB::table('cache')->updateOrInsert(
                    ['key' => $key],
                    ['value' => serialize($value), 'expiration' => time() + self::DEFAULT_TTL]
                );
                break;
        }
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(): float
    {
        $redis = Redis::connection();
        $stats = $redis->info('stats');

        $hits = $stats['keyspace_hits'] ?? 0;
        $misses = $stats['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
}
