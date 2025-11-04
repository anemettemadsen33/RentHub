<?php

namespace App\Console\Commands;

use App\Services\LoyaltyService;
use Illuminate\Console\Command;

class AwardBirthdayBonuses extends Command
{
    protected $signature = 'loyalty:award-birthdays';

    protected $description = 'Award birthday bonuses to users';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Checking for birthday bonuses...');

        $awarded = $loyaltyService->checkBirthdayBonuses();

        $this->info("Awarded birthday bonuses to {$awarded} users");

        return Command::SUCCESS;
    }
}
