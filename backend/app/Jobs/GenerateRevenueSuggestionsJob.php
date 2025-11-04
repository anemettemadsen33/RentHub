<?php

namespace App\Jobs;

use App\Services\AI\PriceOptimizationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateRevenueSuggestionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $propertyId
    ) {}

    public function handle(PriceOptimizationService $priceOptimizationService): void
    {
        try {
            $priceOptimizationService->generateRevenueSuggestions($this->propertyId);
            Log::info("Generated revenue suggestions for property {$this->propertyId}");
        } catch (\Exception $e) {
            Log::error("Failed to generate revenue suggestions for property {$this->propertyId}: " . $e->getMessage());
            throw $e;
        }
    }
}
