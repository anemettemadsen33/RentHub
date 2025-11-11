<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\DataDeletionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDataDeletionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected User $user,
        protected DataDeletionRequest $deletionRequest
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            Log::info('Processing data deletion request', [
                'user_id' => $this->user->id,
                'request_id' => $this->deletionRequest->id,
            ]);

            // Mark request as processing
            $this->deletionRequest->update([
                'status' => 'processing',
                'processed_at' => now(),
            ]);

            // Perform data deletion based on CCPA/GDPR requirements
            // This should be implemented according to your data retention policies

            // Example deletions (customize based on your needs):
            // - Anonymize or delete user data
            // - Remove associated records
            // - Archive necessary data for compliance

            // Mark as completed
            $this->deletionRequest->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            Log::info('Data deletion request completed', [
                'user_id' => $this->user->id,
                'request_id' => $this->deletionRequest->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Data deletion request failed', [
                'user_id' => $this->user->id,
                'request_id' => $this->deletionRequest->id,
                'error' => $e->getMessage(),
            ]);

            $this->deletionRequest->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Data deletion job permanently failed', [
            'user_id' => $this->user->id,
            'request_id' => $this->deletionRequest->id,
            'exception' => $exception->getMessage(),
        ]);

        $this->deletionRequest->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
        ]);
    }
}
