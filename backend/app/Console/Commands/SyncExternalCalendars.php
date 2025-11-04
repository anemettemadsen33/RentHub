<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ExternalCalendar;
use App\Services\ICalService;

class SyncExternalCalendars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'calendar:sync 
                            {--property= : Sync only calendars for a specific property}
                            {--force : Force sync even if recently synced}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all external calendars (iCal, Airbnb, Booking.com) to update property availability';

    /**
     * Execute the console command.
     */
    public function handle(ICalService $icalService)
    {
        $this->info('Starting external calendar sync...');

        $query = ExternalCalendar::where('sync_enabled', true);

        if ($propertyId = $this->option('property')) {
            $query->where('property_id', $propertyId);
            $this->info("Filtering by property ID: {$propertyId}");
        }

        // Only sync calendars that haven't been synced in the last hour (unless forced)
        if (!$this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('last_synced_at')
                  ->orWhere('last_synced_at', '<', now()->subHour());
            });
        }

        $calendars = $query->with('property')->get();

        if ($calendars->isEmpty()) {
            $this->info('No calendars to sync.');
            return 0;
        }

        $this->info("Found {$calendars->count()} calendars to sync.");

        $successCount = 0;
        $failCount = 0;
        $totalAdded = 0;

        $bar = $this->output->createProgressBar($calendars->count());
        $bar->start();

        foreach ($calendars as $calendar) {
            try {
                $result = $icalService->syncExternalCalendar($calendar);

                // Log the sync
                $calendar->syncLogs()->create([
                    'status' => $result['success'] ? 'success' : 'failed',
                    'dates_added' => $result['dates_added'] ?? 0,
                    'dates_removed' => $result['dates_removed'] ?? 0,
                    'error_message' => $result['error'] ?? null,
                    'metadata' => $result,
                    'synced_at' => now(),
                ]);

                if ($result['success']) {
                    $successCount++;
                    $totalAdded += $result['dates_added'] ?? 0;
                } else {
                    $failCount++;
                    $this->newLine();
                    $this->error("Failed to sync calendar #{$calendar->id}: {$result['error']}");
                }
            } catch (\Exception $e) {
                $failCount++;
                $this->newLine();
                $this->error("Error syncing calendar #{$calendar->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info("Sync completed!");
        $this->table(
            ['Metric', 'Count'],
            [
                ['Successful syncs', $successCount],
                ['Failed syncs', $failCount],
                ['Total dates added', $totalAdded],
            ]
        );

        return $failCount > 0 ? 1 : 0;
    }
}
