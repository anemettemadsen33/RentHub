<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CleaningSchedule;
use Illuminate\Support\Facades\Log;

class ProcessCleaningSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleaning:process-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process due cleaning schedules and create cleaning service bookings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing cleaning schedules...');

        $schedules = CleaningSchedule::dueForExecution()->get();

        if ($schedules->isEmpty()) {
            $this->info('No schedules due for execution.');
            return 0;
        }

        $processed = 0;
        $errors = 0;

        foreach ($schedules as $schedule) {
            try {
                $cleaningService = $schedule->execute();
                
                if ($cleaningService) {
                    $this->info("Created cleaning service #{$cleaningService->id} for property #{$schedule->property_id}");
                    $processed++;
                } else {
                    $this->warn("Schedule #{$schedule->id} skipped (auto_book disabled)");
                }
            } catch (\Exception $e) {
                $this->error("Error processing schedule #{$schedule->id}: {$e->getMessage()}");
                Log::error("Error processing cleaning schedule", [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errors++;
            }
        }

        $this->info("Processing complete: {$processed} cleaning services created, {$errors} errors.");
        return 0;
    }
}
