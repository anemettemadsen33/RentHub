<?php

namespace App\Console\Commands;

use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class UpdateExchangeRates extends Command
{
    protected $signature = 'exchange-rates:update';
    protected $description = 'Update currency exchange rates from external API';

    public function __construct(
        private ExchangeRateService $exchangeRateService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('Updating exchange rates...');

        try {
            $this->exchangeRateService->updateExchangeRates();
            $this->info('✓ Exchange rates updated successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to update exchange rates: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
