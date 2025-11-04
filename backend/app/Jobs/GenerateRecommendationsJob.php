<?php

namespace App\Jobs;

use App\Services\AI\RecommendationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateRecommendationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $userId
    ) {}

    public function handle(RecommendationService $recommendationService): void
    {
        try {
            $recommendationService->generateRecommendations($this->userId);
            Log::info("Generated recommendations for user {$this->userId}");
        } catch (\Exception $e) {
            Log::error("Failed to generate recommendations for user {$this->userId}: ".$e->getMessage());
            throw $e;
        }
    }
}
