<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Services\ExchangeRateService;
use Illuminate\Http\Request;

class MultiCurrencyController extends Controller
{
    protected $exchangeRateService;

    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
    }

    /**
     * Get supported currencies
     */
    public function index()
    {
        return response()->json([
            'currencies' => $this->exchangeRateService->getSupportedCurrencies(),
        ]);
    }

    /**
     * Get exchange rates
     */
    public function rates(Request $request)
    {
        $base = $request->input('base', 'USD');

        return response()->json([
            'base' => $base,
            'rates' => $this->exchangeRateService->getExchangeRates($base),
            'updated_at' => now(),
        ]);
    }

    /**
     * Convert currency
     */
    public function convert(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
        ]);

        $result = $this->exchangeRateService->convert(
            $request->input('amount'),
            $request->input('from'),
            $request->input('to')
        );

        return response()->json($result);
    }
}

