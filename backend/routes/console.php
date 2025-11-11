<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule external calendar sync every 6 hours
Schedule::command('calendar:sync')->everySixHours()->withoutOverlapping();

// Schedule Google Calendar webhook renewal daily
Schedule::command('google-calendar:renew-webhooks')->daily()->withoutOverlapping();

// Schedule saved search alerts
Schedule::command('saved-searches:send-alerts instant')->hourly()->withoutOverlapping();
Schedule::command('saved-searches:send-alerts daily')->daily()->withoutOverlapping();
Schedule::command('saved-searches:send-alerts weekly')->weekly()->withoutOverlapping();

// Schedule exchange rate updates daily
Schedule::command('exchange-rates:update')->daily()->withoutOverlapping();

