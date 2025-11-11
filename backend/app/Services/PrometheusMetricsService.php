<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;

class PrometheusMetricsService
{
    /**
     * Export metrics in Prometheus format
     */
    public function export(): string
    {
        $lines = [];

        // Add help and type annotations
        $lines[] = '# HELP http_requests_total Total number of HTTP requests';
        $lines[] = '# TYPE http_requests_total counter';

        // Get HTTP request counters
        $counters = $this->getCounters('http_requests');
        foreach ($counters as $labels => $value) {
            $lines[] = "http_requests_total{{$labels}} {$value}";
        }

        // Add request duration histogram
        $lines[] = '';
        $lines[] = '# HELP http_request_duration_seconds HTTP request latency in seconds';
        $lines[] = '# TYPE http_request_duration_seconds histogram';

        $histograms = $this->getHistograms('http_request_duration');
        foreach ($histograms as $metric) {
            $lines[] = $metric;
        }

        // Add cache metrics
        $lines[] = '';
        $lines[] = '# HELP cache_hits_total Total number of cache hits';
        $lines[] = '# TYPE cache_hits_total counter';
        try {
            $cacheHits = (int) Redis::get('cache:hits');
            $lines[] = "cache_hits_total {$cacheHits}";
        } catch (\Exception $e) {
            $lines[] = 'cache_hits_total 0';
        }

        $lines[] = '';
        $lines[] = '# HELP cache_misses_total Total number of cache misses';
        $lines[] = '# TYPE cache_misses_total counter';
        try {
            $cacheMisses = (int) Redis::get('cache:misses');
            $lines[] = "cache_misses_total {$cacheMisses}";
        } catch (\Exception $e) {
            $lines[] = 'cache_misses_total 0';
        }

        // Add queue metrics
        $lines[] = '';
        $lines[] = '# HELP queue_depth Current number of jobs in queue';
        $lines[] = '# TYPE queue_depth gauge';

        $queues = ['default', 'notifications', 'emails', 'high', 'low'];
        foreach ($queues as $queue) {
            try {
                $size = Redis::llen("queues:{$queue}");
                $lines[] = "queue_depth{queue=\"{$queue}\"} {$size}";
            } catch (\Exception $e) {
                $lines[] = "queue_depth{queue=\"{$queue}\"} 0";
            }
        }

        $lines[] = '';
        $lines[] = '# HELP queue_jobs_processed_total Total number of processed jobs';
        $lines[] = '# TYPE queue_jobs_processed_total counter';
        try {
            $processed = (int) Redis::get('queue:metrics:processed:total');
            $lines[] = "queue_jobs_processed_total {$processed}";
        } catch (\Exception $e) {
            $lines[] = 'queue_jobs_processed_total 0';
        }

        $lines[] = '';
        $lines[] = '# HELP queue_jobs_failed_total Total number of failed jobs';
        $lines[] = '# TYPE queue_jobs_failed_total counter';
        try {
            $failed = \DB::table('failed_jobs')->count();
            $lines[] = "queue_jobs_failed_total {$failed}";
        } catch (\Exception $e) {
            $lines[] = 'queue_jobs_failed_total 0';
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * Get counter metrics from Redis
     */
    protected function getCounters(string $prefix): array
    {
        $counters = [];
        
        try {
            $keys = Redis::keys("metrics:counter:{$prefix}:*");

            foreach ($keys as $key) {
                // Extract labels from key
                $parts = explode(':', $key);
                $labelPart = end($parts);

                $value = (int) Redis::get($key);
                $counters[$labelPart] = $value;
            }
        } catch (\Exception $e) {
            // Redis not available, return empty
        }

        return $counters;
    }

    /**
     * Get histogram metrics from Redis
     */
    protected function getHistograms(string $prefix): array
    {
        $lines = [];
        
        try {
            $keys = Redis::keys("metrics:histogram:{$prefix}:*");

            foreach ($keys as $key) {
                $values = Redis::lrange($key, 0, -1);
                if (empty($values)) {
                    continue;
                }

                // Calculate percentiles
                $sorted = array_map('floatval', $values);
                sort($sorted);
                $count = count($sorted);

                // Extract labels
                $parts = explode(':', $key);
                $labelPart = $parts[count($parts) - 1] ?? '';

                // Histogram buckets (in seconds)
                $buckets = [0.005, 0.01, 0.025, 0.05, 0.1, 0.25, 0.5, 1.0, 2.5, 5.0, 10.0];
                $cumulative = 0;

                foreach ($buckets as $bucket) {
                    $cumulative += count(array_filter($sorted, fn ($v) => $v <= $bucket * 1000));
                    $lines[] = "http_request_duration_seconds_bucket{{$labelPart},le=\"{$bucket}\"} {$cumulative}";
                }

                $lines[] = "http_request_duration_seconds_bucket{{$labelPart},le=\"+Inf\"} {$count}";
                $lines[] = "http_request_duration_seconds_sum{{$labelPart}} ".array_sum($sorted) / 1000;
                $lines[] = "http_request_duration_seconds_count{{$labelPart}} {$count}";
            }
        } catch (\Exception $e) {
            // Redis not available, return empty
        }

        return $lines;
    }

    /**
     * Get cache hit rate
     */
    public function getCacheHitRate(): float
    {
        $hits = (int) Redis::get('cache:hits');
        $misses = (int) Redis::get('cache:misses');
        $total = $hits + $misses;

        return $total > 0 ? round(($hits / $total) * 100, 2) : 0;
    }
}
