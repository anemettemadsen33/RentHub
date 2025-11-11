<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class TrackQueueMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Increment processed counter
        Redis::incr('queue:metrics:processed:total');
        Redis::incr('queue:metrics:processed:today');

        // Reset daily counter at midnight
        $lastReset = Redis::get('queue:metrics:last_reset');
        if (! $lastReset || $lastReset < now()->startOfDay()->timestamp) {
            Redis::set('queue:metrics:processed:today', 0);
            Redis::set('queue:metrics:last_reset', now()->timestamp);
        }
    }

    /**
     * The job failed to process.
     */
    public function failed(\Throwable $exception): void
    {
        Redis::incr('queue:metrics:failed:total');
        Redis::incr('queue:metrics:failed:today');
    }
}
