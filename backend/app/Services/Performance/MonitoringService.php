<?php

namespace App\Services\Performance;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class MonitoringService
{
    /**
     * Get application performance metrics
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'response_times' => $this->getAverageResponseTimes(),
            'database_metrics' => $this->getDatabaseMetrics(),
            'cache_metrics' => $this->getCacheMetrics(),
            'memory_usage' => $this->getMemoryUsage(),
            'cpu_usage' => $this->getCPUUsage(),
            'active_users' => $this->getActiveUsers(),
        ];
    }

    /**
     * Get average response times
     */
    protected function getAverageResponseTimes(): array
    {
        $key = 'monitoring:response_times';
        $times = Redis::lrange($key, 0, -1);

        if (empty($times)) {
            return [
                'average' => 0,
                'min' => 0,
                'max' => 0,
                'p95' => 0,
                'p99' => 0,
            ];
        }

        $times = array_map('floatval', $times);
        sort($times);

        return [
            'average' => round(array_sum($times) / count($times), 2),
            'min' => round(min($times), 2),
            'max' => round(max($times), 2),
            'p95' => round($this->percentile($times, 95), 2),
            'p99' => round($this->percentile($times, 99), 2),
        ];
    }

    /**
     * Get database metrics
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            $status = DB::select('SHOW STATUS LIKE "Threads_connected"');
            $maxConnections = DB::select('SHOW VARIABLES LIKE "max_connections"');

            return [
                'connections' => [
                    'current' => $status[0]->Value ?? 0,
                    'max' => $maxConnections[0]->Value ?? 0,
                ],
                'slow_queries' => $this->getSlowQueryCount(),
                'query_cache_hit_rate' => $this->getQueryCacheHitRate(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get cache metrics
     */
    protected function getCacheMetrics(): array
    {
        try {
            $info = Redis::info();

            return [
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'keys' => $this->getCacheKeyCount(),
                'hit_rate' => $this->getCacheHitRate(),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Get memory usage
     */
    protected function getMemoryUsage(): array
    {
        return [
            'current' => round(memory_get_usage() / 1024 / 1024, 2).' MB',
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2).' MB',
        ];
    }

    /**
     * Get CPU usage (Unix only)
     */
    protected function getCPUUsage(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'Not available on Windows';
        }

        $load = sys_getloadavg();

        return round($load[0], 2).'%';
    }

    /**
     * Get active users count
     */
    protected function getActiveUsers(): int
    {
        return DB::table('active_sessions')
            ->where('last_activity', '>', now()->subMinutes(15))
            ->distinct('user_id')
            ->count();
    }

    /**
     * Record response time
     */
    public function recordResponseTime(float $time): void
    {
        $key = 'monitoring:response_times';
        Redis::lpush($key, $time);
        Redis::ltrim($key, 0, 999); // Keep last 1000 entries
    }

    /**
     * Get health status
     */
    public function getHealthStatus(): array
    {
        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
            'queue' => $this->checkQueue(),
        ];

        $healthy = array_reduce($checks, fn ($carry, $check) => $carry && $check['status'], true);

        return [
            'healthy' => $healthy,
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ];
    }

    protected function checkDatabase(): array
    {
        try {
            DB::select('SELECT 1');

            return ['status' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkCache(): array
    {
        try {
            Cache::put('health_check', true, 10);
            $result = Cache::get('health_check');

            return ['status' => $result === true, 'message' => 'Cache OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkStorage(): array
    {
        try {
            $path = storage_path('app');
            $freeSpace = disk_free_space($path);
            $totalSpace = disk_total_space($path);
            $usedPercent = round(($totalSpace - $freeSpace) / $totalSpace * 100, 2);

            return [
                'status' => $usedPercent < 90,
                'message' => "Storage: {$usedPercent}% used",
                'free_space' => $this->formatBytes($freeSpace),
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function checkQueue(): array
    {
        try {
            $pendingJobs = Redis::llen('queues:default');

            return [
                'status' => true,
                'message' => 'Queue OK',
                'pending_jobs' => $pendingJobs,
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage()];
        }
    }

    protected function percentile(array $sorted, int $percentile): float
    {
        $index = ceil(count($sorted) * $percentile / 100) - 1;

        return $sorted[$index] ?? 0;
    }

    protected function getSlowQueryCount(): int
    {
        try {
            $result = DB::select('SHOW STATUS LIKE "Slow_queries"');

            return $result[0]->Value ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getQueryCacheHitRate(): float
    {
        // Placeholder - implement based on your caching strategy
        return 0.0;
    }

    protected function getCacheKeyCount(): int
    {
        try {
            return Redis::dbsize();
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getCacheHitRate(): float
    {
        try {
            $hits = (int) Redis::get('cache:stats:hits') ?? 0;
            $misses = (int) Redis::get('cache:stats:misses') ?? 0;
            $total = $hits + $misses;

            return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2).' '.$units[$i];
    }
}
