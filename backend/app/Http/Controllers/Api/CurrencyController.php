<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Services\ExchangeRateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function __construct(
        private ExchangeRateService $exchangeRateService
    ) {}

    public function index(): JsonResponse
    {
        $currencies = Currency::getActive();

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $currency = Currency::where('code', $code)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $currency,
        ]);
    }

    public function getDefault(): JsonResponse
    {
        $currency = Currency::getDefault();

        if (!$currency) {
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
