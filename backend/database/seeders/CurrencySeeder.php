<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run()
    {
        $currencies = [
            ['code' => 'USD', 'symbol' => '$', 'name' => 'US Dollar', 'decimal_places' => 2],
            ['code' => 'EUR', 'symbol' => '€', 'name' => 'Euro', 'decimal_places' => 2],
            ['code' => 'GBP', 'symbol' => '£', 'name' => 'British Pound', 'decimal_places' => 2],
            ['code' => 'JPY', 'symbol' => '¥', 'name' => 'Japanese Yen', 'decimal_places' => 0],
            ['code' => 'CAD', 'symbol' => 'C$', 'name' => 'Canadian Dollar', 'decimal_places' => 2],
            ['code' => 'AUD', 'symbol' => 'A$', 'name' => 'Australian Dollar', 'decimal_places' => 2],
            ['code' => 'RON', 'symbol' => 'lei', 'name' => 'Romanian Leu', 'decimal_places' => 2],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(['code' => $currency['code']], $currency);
        }

        // Create exchange rates (USD as base)
        $usd = Currency::where('code', 'USD')->first();
        $rates = [
            ['to' => 'EUR', 'rate' => 0.92],
            ['to' => 'GBP', 'rate' => 0.79],
            ['to' => 'JPY', 'rate' => 149.50],
            ['to' => 'CAD', 'rate' => 1.37],
            ['to' => 'AUD', 'rate' => 1.53],
            ['to' => 'RON', 'rate' => 4.57],
        ];

        foreach ($rates as $rate) {
            $toCurrency = Currency::where('code', $rate['to'])->first();
            if ($usd && $toCurrency) {
                ExchangeRate::updateOrCreate(
                    ['from_currency_id' => $usd->id, 'to_currency_id' => $toCurrency->id],
                    [
                        'rate' => $rate['rate'], 
                        'source' => 'manual',
                        'fetched_at' => now(),
                    ]
                );
            }
        }
    }
}
