<?php

namespace App\Jobs;

use App\Services\AI\PriceOptimizationService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePricePredictionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $propertyId,
        public Carbon $startDate,
        public Carbon $endDate
    ) {}

    public function handle(PriceOptimizationService $priceOptimizationService): void
    {
        try {
            $priceOptimizationService->predictPrices($this->propertyId, $this->startDate, $this->endDate);
            Log::info("Generated price predictions for property {$this->propertyId}");
        } catch (\Exception $e) {
            Log::error("Failed to generate price predictions for property {$this->propertyId}: " . $e->getMessage());
            throw $e;
        }
    }
}
