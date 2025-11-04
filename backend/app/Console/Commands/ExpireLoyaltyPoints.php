<?php

namespace App\Console\Commands;

use App\Services\LoyaltyService;
use Illuminate\Console\Command;

class ExpireLoyaltyPoints extends Command
{
    protected $signature = 'loyalty:expire-points';

    protected $description = 'Expire old loyalty points';

    public function handle(LoyaltyService $loyaltyService): int
    {
        $this->info('Expiring old loyalty points...');

        $expiredCount = $loyaltyService->expirePoints();

        $this->info("Expired {$expiredCount} points");

        return Command::SUCCESS;
    }
}
