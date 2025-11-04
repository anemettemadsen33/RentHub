<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Services\AI\PriceOptimizationService;
use App\Models\Property;
use App\Models\PricePrediction;
use App\Models\RevenueSuggestion;
use App\Models\OccupancyPrediction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class PriceOptimizationController extends Controller
{
    public function __construct(
        private PriceOptimizationService $priceOptimizationService
    ) {}

    /**
     * Get price predictions for a property
     */
    public function getPricePredictions(Request $request, int $propertyId): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $property = Property::findOrFail($propertyId);
        
        // Check if user owns the property
        if ($property->owner_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $predictions = $this->priceOptimizationService->predictPrices(
            $propertyId,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get revenue optimization suggestions
     */
    public function getRevenueSuggestions(int $propertyId): JsonResponse
    {
        $property = Property::findOrFail($propertyId);
        
        if ($property->owner_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $suggestions = $this->priceOptimizationService->generateRevenueSuggestions($propertyId);

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Apply revenue suggestion
     */
    public function applySuggestion(int $suggestionId): JsonResponse
    {
        $suggestion = RevenueSuggestion::findOrFail($suggestionId);
        $property = $suggestion->property;
        
        if ($property->owner_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Apply the suggestion based on type
        match ($suggestion->suggestion_type) {
            'price_increase', 'price_decrease' => 
                $property->update(['price_per_night' => $suggestion->parameters['suggested_price']]),
            'minimum_stay' => 
                $property->update(['minimum_stay' => $suggestion->parameters['suggested_min_stay']]),
            default => null,
        };

        $suggestion->apply();

        return response()->json([
            'success' => true,
            'message' => 'Suggestion applied successfully',
            'data' => $suggestion,
        ]);
    }

    /**
     * Get occupancy predictions
     */
    public function getOccupancyPredictions(Request $request, int $propertyId): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $property = Property::findOrFail($propertyId);
        
        if ($property->owner_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $predictions = $this->priceOptimizationService->predictOccupancy(
            $propertyId,
            Carbon::parse($request->start_date),
            Carbon::parse($request->end_date)
        );

        return response()->json([
            'success' => true,
            'data' => $predictions,
        ]);
    }

    /**
     * Get historical predictions vs actuals
     */
    public function getPredictionAccuracy(int $propertyId): JsonResponse
    {
        $property = Property::findOrFail($propertyId);
        
        if ($property->owner_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $predictions = PricePrediction::where('property_id', $propertyId)
            ->whereNotNull('actual_price')
            ->orderByDesc('date')
            ->limit(90)
            ->get();

        $mae = $predictions->avg(function ($pred) {
            return abs($pred->predicted_price - $pred->actual_price);
        });

        $rmse = sqrt($predictions->avg(function ($pred) {
            return pow($pred->predicted_price - $pred->actual_price, 2);
        }));

        return response()->json([
            'success' => true,
            'data' => [
                'predictions' => $predictions,
                'metrics' => [
                    'mae' => round($mae, 2),
                    'rmse' => round($rmse, 2),
                    'sample_size' => $predictions->count(),
                ],
            ],
        ]);
    }
}
