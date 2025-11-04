<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    private const API_URL = 'https://api.exchangerate-api.com/v4/latest/';

    public function updateExchangeRates(): void
    {
        $baseCurrency = Currency::getDefault();

        if (! $baseCurrency) {
            Log::error('No default currency set');

            return;
        }

        try {
            $response = Http::get(self::API_URL.$baseCurrency->code);

            if (! $response->successful()) {
                throw new \Exception('Failed to fetch exchange rates');
            }

            $data = $response->json();
            $rates = $data['rates'] ?? [];

            foreach ($rates as $currencyCode => $rate) {
                $targetCurrency = Currency::where('code', $currencyCode)->first();

                if ($targetCurrency && $targetCurrency->id !== $baseCurrency->id) {
                    ExchangeRate::updateOrCreate(
                        [
                            'from_currency_id' => $baseCurrency->id,
                            'to_currency_id' => $targetCurrency->id,
                        ],
                        [
                            'rate' => $rate,
                            'fetched_at' => now(),
                            'source' => 'exchangerate-api.com',
                        ]
                    );

                    // Create reverse rate
                    ExchangeRate::updateOrCreate(
                        [
                            'from_currency_id' => $targetCurrency->id,
                            'to_currency_id' => $baseCurrency->id,
                        ],
                        [
                            'rate' => 1 / $rate,
                            'fetched_at' => now(),
                            'source' => 'exchangerate-api.com',
                        ]
                    );
                }
            }

            Log::info('Exchange rates updated successfully');
        } catch (\Exception $e) {
            Log::error('Failed to update exchange rates: '.$e->getMessage());
            throw $e;
        }
    }

    public function convert(string $fromCode, string $toCode, float $amount): array
    {
        $fromCurrency = Currency::where('code', $fromCode)->firstOrFail();
        $toCurrency = Currency::where('code', $toCode)->firstOrFail();

        $convertedAmount = $fromCurrency->convertTo($toCurrency, $amount);

        return [
            'from' => [
                'currency' => $fromCode,
                'amount' => $amount,
                'formatted' => $fromCurrency->format($amount),
            ],
            'to' => [
                'currency' => $toCode,
                'amount' => $convertedAmount,
                'formatted' => $toCurrency->format($convertedAmount),
            ],
            'rate' => ExchangeRate::getRate($fromCurrency->id, $toCurrency->id),
        ];
    }
}
