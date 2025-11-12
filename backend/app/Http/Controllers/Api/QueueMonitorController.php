<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class QueueMonitorController extends Controller
{
    /**
     * Get queue statistics and health
     */
    public function index(): JsonResponse
    {
        $stats = [
            'queues' => $this->getQueueStats(),
            'failed_jobs' => $this->getFailedJobStats(),
            'recent_jobs' => $this->getRecentJobs(),
            'health' => $this->getQueueHealth(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get statistics for each queue
     */
    protected function getQueueStats(): array
    {
        $queues = ['default', 'notifications', 'emails', 'high', 'low'];
        $stats = [];

        foreach ($queues as $queueName) {
            try {
                $size = Redis::llen("queues:{$queueName}");
                $stats[$queueName] = [
                    'name' => $queueName,
                    'size' => (int) $size,
                    'status' => $size > 100 ? 'warning' : 'healthy',
                ];
            } catch (\Exception $e) {
                $stats[$queueName] = [
                    'name' => $queueName,
                    'size' => 0,
                    'status' => 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return array_values($stats);
    }

    /**
     * Get failed job statistics
     */
    protected function getFailedJobStats(): array
    {
        try {
            $failedJobs = DB::table('failed_jobs')
                ->select(
                    DB::raw('COUNT(*) as total'),
                    DB::raw('COUNT(CASE WHEN failed_at > NOW() - INTERVAL 1 HOUR THEN 1 END) as last_hour'),
                    DB::raw('COUNT(CASE WHEN failed_at > NOW() - INTERVAL 24 HOUR THEN 1 END) as last_24_hours')
                )
                ->first();

            $recentFailures = DB::table('failed_jobs')
                ->select('queue', 'exception', 'failed_at')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($job) {
                    return [
                        'queue' => $job->queue,
                        'error' => substr($job->exception, 0, 200),
                        'failed_at' => $job->failed_at,
                    ];
                });

            return [
                'total' => (int) $failedJobs->total,
                'last_hour' => (int) $failedJobs->last_hour,
                'last_24_hours' => (int) $failedJobs->last_24_hours,
                'recent_failures' => $recentFailures->toArray(),
            ];
        } catch (\Exception $e) {
            return [
                'total' => 0,
                'last_hour' => 0,
                'last_24_hours' => 0,
                'recent_failures' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent processed jobs info
     */
    protected function getRecentJobs(): array
    {
        try {
            // Get recent job metrics from Redis (if stored)
            $processed = Redis::get('queue:metrics:processed:total') ?? 0;
            $processedToday = Redis::get('queue:metrics:processed:today') ?? 0;

            return [
                'processed_total' => (int) $processed,
                'processed_today' => (int) $processedToday,
            ];
        } catch (\Exception $e) {
            return [
                'processed_total' => 0,
                'processed_today' => 0,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Determine overall queue health
     */
    protected function getQueueHealth(): array
    {
        $queueStats = $this->getQueueStats();
        $failedStats = $this->getFailedJobStats();

        $totalQueued = array_sum(array_column($queueStats, 'size'));
        $recentFailures = $failedStats['last_hour'];

        $status = 'healthy';
        $issues = [];

        if ($totalQueued > 500) {
            $status = 'degraded';
            $issues[] = "High queue depth: {$totalQueued} jobs pending";
        }

        if ($recentFailures > 10) {
            $status = 'unhealthy';
            $issues[] = "{$recentFailures} jobs failed in the last hour";
        }

        return [
            'status' => $status,
            'total_queued' => $totalQueued,
            'recent_failures' => $recentFailures,
            'issues' => $issues,
            'checked_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Retry a failed job
     */
    public function retryFailed(string $id): JsonResponse
    {
        try {
            $job = DB::table('failed_jobs')->where('id', $id)->first();

            if (! $job) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed job not found',
                ], 404);
            }

            // Delete from failed_jobs and re-queue
            DB::table('failed_jobs')->where('id', $id)->delete();

            Queue::push($job->payload);

            return response()->json([
                'success' => true,
                'message' => 'Job queued for retry',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all failed jobs
     */
    public function clearFailed(): JsonResponse
    {
        try {
            $count = DB::table('failed_jobs')->count();
            DB::table('failed_jobs')->truncate();

            return response()->json([
                'success' => true,
                'message' => "Cleared {$count} failed jobs",
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

