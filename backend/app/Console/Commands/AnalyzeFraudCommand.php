<?php

namespace App\Console\Commands;

use App\Jobs\AnalyzeFraudJob;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Console\Command;

class AnalyzeFraudCommand extends Command
{
    protected $signature = 'ai:analyze-fraud {type : Type: user, property, booking, payment, review, all} {--id= : Specific entity ID}';

    protected $description = 'Analyze entities for fraud using AI';

    public function handle(): int
    {
        $type = $this->argument('type');

        if ($this->option('id')) {
            if ($type === 'all') {
                $this->error('Cannot use --id with type "all"');

                return 1;
            }

            AnalyzeFraudJob::dispatch($type, $this->option('id'));
            $this->info("Queued fraud analysis for {$type} #{$this->option('id')}");

            return 0;
        }

        if ($type === 'all') {
            $this->analyzeAll();

            return 0;
        }

        $entities = match ($type) {
            'user' => User::where('created_at', '>', now()->subDays(30))->get(),
            'property' => Property::where('created_at', '>', now()->subDays(30))->get(),
            'booking' => Booking::where('created_at', '>', now()->subDays(7))->get(),
            'payment' => Payment::where('created_at', '>', now()->subDays(7))->get(),
            'review' => Review::where('created_at', '>', now()->subDays(7))->get(),
            default => collect(),
        };

        if ($entities->isEmpty()) {
            $this->info("No recent {$type} entities to analyze");

            return 0;
        }

        $this->info("Queuing fraud analysis for {$entities->count()} {$type} entities...");

        $bar = $this->output->createProgressBar($entities->count());
        $bar->start();

        foreach ($entities as $entity) {
            AnalyzeFraudJob::dispatch($type, $entity->id);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Queued fraud analysis for {$entities->count()} {$type} entities");

        return 0;
    }

    private function analyzeAll(): void
    {
        $types = ['user', 'property', 'booking', 'payment', 'review'];

        foreach ($types as $type) {
            $this->call('ai:analyze-fraud', ['type' => $type]);
        }
    }
}
