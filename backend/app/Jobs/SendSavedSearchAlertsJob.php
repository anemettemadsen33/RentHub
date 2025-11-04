<?php

namespace App\Jobs;

use App\Models\SavedSearch;
use App\Notifications\NewListingsAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSavedSearchAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $frequency = 'daily'
    ) {}

    public function handle(): void
    {
        $savedSearches = SavedSearch::query()
            ->where('is_active', true)
            ->where('enable_alerts', true)
            ->where('alert_frequency', $this->frequency)
            ->with('user')
            ->get();

        Log::info("Processing {$savedSearches->count()} saved searches for {$this->frequency} alerts");

        foreach ($savedSearches as $savedSearch) {
            try {
                // Check if it's time to send alert based on frequency
                if (!$this->shouldSendAlert($savedSearch)) {
                    continue;
                }

                // Find new listings
                $newProperties = $savedSearch->checkNewListings();

                if ($newProperties->isEmpty()) {
                    Log::info("No new properties for saved search #{$savedSearch->id}");
                    continue;
                }

                // Send notification
                $savedSearch->user->notify(
                    new NewListingsAlertNotification($savedSearch, $newProperties)
                );

                // Update alert metadata
                $savedSearch->update([
                    'last_alert_sent_at' => now(),
                    'new_listings_count' => $newProperties->count(),
                ]);

                Log::info("Sent alert for saved search #{$savedSearch->id} with {$newProperties->count()} properties");
            } catch (\Exception $e) {
                Log::error("Failed to process saved search #{$savedSearch->id}: " . $e->getMessage());
            }
        }
    }

    private function shouldSendAlert(SavedSearch $savedSearch): bool
    {
        if (!$savedSearch->last_alert_sent_at) {
            return true; // First alert
        }

        $hoursSinceLastAlert = now()->diffInHours($savedSearch->last_alert_sent_at);

        return match ($this->frequency) {
            'instant' => $hoursSinceLastAlert >= 1, // Max once per hour for instant
            'daily' => $hoursSinceLastAlert >= 24,
            'weekly' => $hoursSinceLastAlert >= 168,
            default => false,
        };
    }
}
