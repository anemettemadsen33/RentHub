<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array<class-string>
     */
    protected $commands = [
        \App\Console\Commands\CreateAdmin::class,
        \App\Console\Commands\FixPropertiesStatus::class,
    ];
    protected function schedule(Schedule $schedule): void
    {
        // Daily prune analytics events at 02:15 server time
        $schedule->command('analytics:prune --days=90')->dailyAt('02:15');
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        // You can list individual command classes here if needed
    }
}
