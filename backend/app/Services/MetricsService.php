<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MetricsService
{
    private const CACHE_PREFIX = 'metrics:';

    private const TTL = 3600; // 1 hour

    /**
     * Increment a counter metric
     */
    public function incrementCounter(string $name, int $value = 1, array $labels = []): void
    {
        $key = $this->buildKey('counter', $name, $labels);

        try {
            Redis::incrby($key, $value);
            Redis::expire($key, self::TTL);
        } catch (\Throwable $e) {
            // Fallback to cache driver if Redis unavailable
            $current = (int) Cache::get($key, 0);
            Cache::put($key, $current + $value, self::TTL);
        }
    }

    /**
     * Record a histogram value (for latencies, durations)
     */
    public function recordHistogram(string $name, float $value, array $labels = []): void
    {
        $key = $this->buildKey('histogram', $name, $labels);

        try {
            // Store in a sorted set for percentile calculations
            Redis::zadd($key, [$value => microtime(true)]);
            Redis::expire($key, self::TTL);
        } catch (\Throwable $e) {
            // Fallback: store as list
            $values = (array) Cache::get($key, []);
            $values[] = $value;
            Cache::put($key, array_slice($values, -1000), self::TTL); // Keep last 1000
        }
    }

    /**
     * Get all metrics for export
     */
    public function getMetrics(): array
    {
        $metrics = [
            'counters' => $this->getCounters(),
            'histograms' => $this->getHistograms(),
        ];

        return $metrics;
    }

    /**
     * Get all counter metrics
     */
    private function getCounters(): array
    {
        $counters = [];

        try {
            $pattern = self::CACHE_PREFIX.'counter:*';
            $keys = Redis::keys($pattern);

            foreach ($keys as $key) {
                $name = $this->extractNameFromKey($key);
                $counters[$name] = (int) Redis::get($key);
            }
        } catch (\Throwable $e) {
            // Fallback: try scanning known patterns from cache
            // In test/dev without Redis, this may return empty
            \Log::debug('Failed to fetch counter metrics', ['error' => $e->getMessage()]);
        }

        return $counters;
    }

    /**
     * Get histogram metrics with percentiles
     */
    private function getHistograms(): array
    {
        $histograms = [];

        try {
            $pattern = self::CACHE_PREFIX.'histogram:*';
            $keys = Redis::keys($pattern);

            foreach ($keys as $key) {
                $name = $this->extractNameFromKey($key);
                $values = Redis::zrange($key, 0, -1, ['WITHSCORES' => false]);

                if (! empty($values)) {
                    $values = array_map('floatval', $values);
                    $histograms[$name] = [
                        'count' => count($values),
                        'min' => min($values),
                        'max' => max($values),
                        'avg' => array_sum($values) / count($values),
                        'p50' => $this->percentile($values, 50),
                        'p95' => $this->percentile($values, 95),
                        'p99' => $this->percentile($values, 99),
                    ];
                }
            }
        } catch (\Throwable $e) {
            \Log::debug('Failed to fetch histogram metrics', ['error' => $e->getMessage()]);
        }

        return $histograms;
    }

    /**
     * Build a cache key
     */
    private function buildKey(string $type, string $name, array $labels = []): string
    {
        $labelStr = empty($labels) ? '' : ':'.http_build_query($labels, '', ':');

        return self::CACHE_PREFIX."{$type}:{$name}{$labelStr}";
    }

    /**
     * Extract metric name from key
     */
    private function extractNameFromKey(string $key): string
    {
        $parts = explode(':', str_replace(self::CACHE_PREFIX, '', $key));

        return implode(':', array_slice($parts, 1));
    }

    /**
     * Calculate percentile
     */
    private function percentile(array $values, int $percentile): float
    {
        sort($values);
        $index = (int) ceil((count($values) * $percentile / 100)) - 1;

        return $values[max(0, $index)] ?? 0.0;
    }

    /**
     * Reset all metrics (for testing/admin)
     */
    public function reset(): void
    {
        try {
            $pattern = self::CACHE_PREFIX.'*';
            $keys = Redis::keys($pattern);

            if (! empty($keys)) {
                Redis::del($keys);
            }
        } catch (\Throwable $e) {
            Cache::flush();
        }
    }
}
