<?php

namespace App\Console\Commands;

use App\Jobs\GeneratePricePredictionsJob;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Console\Command;

class GeneratePricePredictionsCommand extends Command
{
    protected $signature = 'ai:generate-price-predictions {--property-id= : Specific property ID} {--all : Generate for all active properties} {--days=90 : Number of days ahead}';
    protected $description = 'Generate AI-powered price predictions for properties';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $startDate = now();
        $endDate = now()->addDays($days);

        if ($this->option('property-id')) {
            $property = Property::findOrFail($this->option('property-id'));
            GeneratePricePredictionsJob::dispatch($property->id, $startDate, $endDate);
            $this->info("Queued price predictions for property #{$property->id}");
            return 0;
        }

        if ($this->option('all')) {
            $properties = Property::where('status', 'active')->get();

            $this->info("Queuing price predictions for {$properties->count()} properties...");
            
            $bar = $this->output->createProgressBar($properties->count());
            $bar->start();

            foreach ($properties as $property) {
                GeneratePricePredictionsJob::dispatch($property->id, $startDate, $endDate);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Queued price predictions for {$properties->count()} properties");
            return 0;
        }

        $this->error('Please specify --property-id or --all');
        return 1;
    }
}
