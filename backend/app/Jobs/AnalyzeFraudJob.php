<?php

namespace App\Jobs;

use App\Services\AI\FraudDetectionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AnalyzeFraudJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $type, // user, property, payment, review, booking
        public int $entityId
    ) {}

    public function handle(FraudDetectionService $fraudDetectionService): void
    {
        try {
            $alert = match ($this->type) {
                'user' => $fraudDetectionService->analyzeUserBehavior($this->entityId),
                'property' => $fraudDetectionService->analyzePropertyListing($this->entityId),
                'payment' => $fraudDetectionService->analyzePayment($this->entityId),
                'review' => $fraudDetectionService->analyzeReview($this->entityId),
                'booking' => $fraudDetectionService->analyzeBooking($this->entityId),
                default => null,
            };

            if ($alert) {
                Log::warning("Fraud alert created: {$this->type} #{$this->entityId} - Score: {$alert->fraud_score}");
            } else {
                Log::info("No fraud detected: {$this->type} #{$this->entityId}");
            }
        } catch (\Exception $e) {
            Log::error("Failed to analyze fraud for {$this->type} #{$this->entityId}: " . $e->getMessage());
            throw $e;
        }
    }
}
