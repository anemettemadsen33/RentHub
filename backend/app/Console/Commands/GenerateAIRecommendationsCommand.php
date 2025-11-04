<?php

namespace App\Console\Commands;

use App\Jobs\GenerateRecommendationsJob;
use App\Models\User;
use Illuminate\Console\Command;

class GenerateAIRecommendationsCommand extends Command
{
    protected $signature = 'ai:generate-recommendations {--user-id= : Specific user ID} {--all : Generate for all active users}';

    protected $description = 'Generate AI-powered property recommendations for users';

    public function handle(): int
    {
        if ($this->option('user-id')) {
            $user = User::findOrFail($this->option('user-id'));
            GenerateRecommendationsJob::dispatch($user->id);
            $this->info("Queued recommendation generation for user #{$user->id}");

            return 0;
        }

        if ($this->option('all')) {
            $users = User::where('email_verified_at', '!=', null)
                ->whereHas('bookings')
                ->get();

            $this->info("Queuing recommendation generation for {$users->count()} users...");

            $bar = $this->output->createProgressBar($users->count());
            $bar->start();

            foreach ($users as $user) {
                GenerateRecommendationsJob::dispatch($user->id);
                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
            $this->info("Queued recommendation generation for {$users->count()} users");

            return 0;
        }

        $this->error('Please specify --user-id or --all');

        return 1;
    }
}
