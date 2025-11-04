<?php

namespace App\Console\Commands;

use App\Models\SavedSearch;
use App\Notifications\SavedSearchNewListingsNotification;
use Illuminate\Console\Command;

class SendSavedSearchAlerts extends Command
{
    protected $signature = 'saved-searches:send-alerts {--frequency=}';

    protected $description = 'Send alerts for saved searches with new matching listings';

    public function handle()
    {
        $frequency = $this->option('frequency');

        $query = SavedSearch::where('is_active', true)
            ->where('enable_alerts', true)
            ->with('user');

        // Filter by frequency if specified
        if ($frequency) {
            $query->where('alert_frequency', $frequency);
        }

        // Check if enough time has passed since last alert
        $query->where(function ($q) {
            $q->whereNull('last_alert_sent_at')
                ->orWhere(function ($subQuery) {
                    // Instant: check every hour
                    $subQuery->where('alert_frequency', 'instant')
                        ->where('last_alert_sent_at', '<', now()->subHour());
                })
                ->orWhere(function ($subQuery) {
                    // Daily: check once per day
                    $subQuery->where('alert_frequency', 'daily')
                        ->where('last_alert_sent_at', '<', now()->subDay());
                })
                ->orWhere(function ($subQuery) {
                    // Weekly: check once per week
                    $subQuery->where('alert_frequency', 'weekly')
                        ->where('last_alert_sent_at', '<', now()->subWeek());
                });
        });

        $savedSearches = $query->get();

        $this->info("Processing {$savedSearches->count()} saved searches...");

        $alertsSent = 0;
        $noNewListings = 0;

        foreach ($savedSearches as $savedSearch) {
            $this->line("Checking: {$savedSearch->name} (User: {$savedSearch->user->name})");

            $newProperties = $savedSearch->checkNewListings();

            if ($newProperties->isEmpty()) {
                $this->line('  → No new listings found');
                $noNewListings++;

                continue;
            }

            $this->info("  → Found {$newProperties->count()} new properties!");

            // Send notification
            try {
                $savedSearch->user->notify(
                    new SavedSearchNewListingsNotification($savedSearch, $newProperties)
                );

                // Update alert metadata
                $savedSearch->update([
                    'last_alert_sent_at' => now(),
                    'new_listings_count' => $newProperties->count(),
                ]);

                $alertsSent++;
                $this->info('  ✓ Alert sent successfully');
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send alert: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Summary:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Searches Processed', $savedSearches->count()],
                ['Alerts Sent', $alertsSent],
                ['No New Listings', $noNewListings],
            ]
        );

        return Command::SUCCESS;
    }
}
