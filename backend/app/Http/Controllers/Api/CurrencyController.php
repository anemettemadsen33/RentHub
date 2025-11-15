<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CurrencyController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRateService
    ) {}

    public function index(): JsonResponse
    {
        $currencies = Cache::tags(['currencies'])->remember('active_currencies', 86400, function () {
            return Currency::active()->get();
        });

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $currency = Cache::tags(['currencies'])->remember("currency_{$code}", 86400, function () use ($code) {
            return Currency::where('code', $code)
                ->where('is_active', true)
                ->firstOrFail();
        });

        return response()->json([
            'success' => true,
            'data' => $currency,
        ]);
    }

    public function getDefault(): JsonResponse
    {
        $currency = Cache::tags(['currencies'])->remember('default_currency', 86400, function () {
            return Currency::getDefault();
        });

        if (! $currency) {
            return response()->json([
                'success' => false,
                'message' => 'No default currency set',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $currency,
        ]);
    }

    public function getActive(): JsonResponse
    {
        // Same as index for backward compatibility
        return $this->index();
    }

    public function convert(Request $request): JsonResponse
    {
        $request->validate([
            'from' => 'required|string|size:3',
            'to' => 'required|string|size:3',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $result = $this->exchangeRateService->convert(
                $request->from,
                $request->to,
                $request->amount
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function updateRates(): JsonResponse
    {
        try {
            $this->exchangeRateService->updateExchangeRates();

            return response()->json([
                'success' => true,
                'message' => 'Exchange rates updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}

