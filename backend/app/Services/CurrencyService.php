<?php

namespace App\Services;

use App\Models\Currency;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyService
{
    /**
     * Convert amount from one currency to another
     */
    public function convert(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        $fromRate = $this->getExchangeRate($from);
        $toRate = $this->getExchangeRate($to);

        return ($amount / $fromRate) * $toRate;
    }

    /**
     * Get exchange rate for currency
     */
    public function getExchangeRate(string $code): float
    {
        return Cache::remember("currency_rate_{$code}", 3600, function () use ($code) {
            $currency = Currency::where('code', $code)->first();
            return $currency ? $currency->exchange_rate : 1;
        });
    }

    /**
     * Update exchange rates from API
     */
    public function updateRates(): void
    {
        try {
            $response = Http::get('https://api.exchangerate-api.com/v4/latest/USD');
            
            if ($response->successful()) {
                $rates = $response->json()['rates'];
                
                foreach ($rates as $code => $rate) {
                    Currency::where('code', $code)->update(['exchange_rate' => $rate]);
                }
                
                Cache::flush();
            }
        } catch (\Exception $e) {
            \Log::error('Failed to update currency rates: ' . $e->getMessage());
        }
    }

    /**
     * Format amount with currency
     */
    public function format(float $amount, string $code): string
    {
        $currency = Currency::where('code', $code)->first();
        
        if (!$currency) {
            return number_format($amount, 2);
        }

        return $currency->symbol . ' ' . number_format($amount, 2);
    }
}