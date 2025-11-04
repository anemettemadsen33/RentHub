<?php

namespace App\Console\Commands;

use App\Jobs\SendSavedSearchAlertsJob;
use Illuminate\Console\Command;

class SendSavedSearchAlertsCommand extends Command
{
    protected $signature = 'saved-searches:send-alerts {frequency=daily}';
    protected $description = 'Send alerts for saved searches';

    public function handle()
    {
        $frequency = $this->argument('frequency');

        if (!in_array($frequency, ['instant', 'daily', 'weekly'])) {
            $this->error('Invalid frequency. Use: instant, daily, or weekly');
            return 1;
        }

        $this->info("Dispatching saved search alerts for frequency: {$frequency}");
        
        SendSavedSearchAlertsJob::dispatch($frequency);
        
        $this->info('Job dispatched successfully!');
        return 0;
    }
}
