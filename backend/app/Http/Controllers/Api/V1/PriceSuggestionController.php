<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PriceSuggestion;
use App\Models\Property;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceSuggestionController extends Controller
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Get all price suggestions for a property
     */
    public function index(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $status = $request->query('status', 'pending');
        $query = $property->priceSuggestions();

        if ($status === 'pending') {
            $query->pending();
        } elseif (in_array($status, ['accepted', 'rejected', 'expired'])) {
            $query->where('status', $status);
        }

        $suggestions = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $suggestions,
        ]);
    }

    /**
     * Generate a new price suggestion
     */
    public function store(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Generate suggestion using AI/algorithm
        $suggestion = $this->pricingService->generatePriceSuggestion($property, $startDate, $endDate);

        return response()->json([
            'success' => true,
            'message' => 'Price suggestion generated successfully',
            'data' => $suggestion,
        ], 201);
    }

    /**
     * Get a specific price suggestion
     */
    public function show(Request $request, $propertyId, $suggestionId)
    {
        $property = Property::findOrFail($propertyId);
        $suggestion = PriceSuggestion::where('property_id', $property->id)->findOrFail($suggestionId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $suggestion,
        ]);
    }

    /**
     * Accept a price suggestion
     */
    public function accept(Request $request, $propertyId, $suggestionId)
    {
        $property = Property::findOrFail($propertyId);
        $suggestion = PriceSuggestion::where('property_id', $property->id)->findOrFail($suggestionId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'This suggestion has already been processed',
            ], 400);
        }

        if ($suggestion->isExpired()) {
            $suggestion->update(['status' => 'expired']);

            return response()->json([
                'success' => false,
                'error' => 'This suggestion has expired',
            ], 400);
        }

        $suggestion->accept();

        return response()->json([
            'success' => true,
            'message' => 'Price suggestion accepted and applied',
            'data' => $suggestion->fresh(),
        ]);
    }

    /**
     * Reject a price suggestion
     */
    public function reject(Request $request, $propertyId, $suggestionId)
    {
        $property = Property::findOrFail($propertyId);
        $suggestion = PriceSuggestion::where('property_id', $property->id)->findOrFail($suggestionId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($suggestion->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error' => 'This suggestion has already been processed',
            ], 400);
        }

        $reason = $request->input('reason');
        $suggestion->reject($reason);

        return response()->json([
            'success' => true,
            'message' => 'Price suggestion rejected',
            'data' => $suggestion->fresh(),
        ]);
    }

    /**
     * Get market analysis for a property
     */
    public function marketAnalysis(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get similar properties for market analysis
        $similarProperties = Property::query()
            ->where('id', '!=', $property->id)
            ->where('type', $property->type)
            ->where('bedrooms', $property->bedrooms)
            ->active()
            ->limit(20)
            ->get();

        $marketData = [
            'property_price' => $property->price_per_night,
            'competitor_count' => $similarProperties->count(),
            'market_average' => $similarProperties->avg('price_per_night'),
            'market_min' => $similarProperties->min('price_per_night'),
            'market_max' => $similarProperties->max('price_per_night'),
            'competitors' => $similarProperties->map(function ($p) {
                return [
                    'id' => $p->id,
                    'title' => $p->title,
                    'price' => $p->price_per_night,
                    'rating' => $p->averageRating,
                    'reviews' => $p->totalReviews,
                    'city' => $p->city,
                ];
            }),
        ];

        return response()->json([
            'success' => true,
            'data' => $marketData,
        ]);
    }

    /**
     * Get pricing optimization recommendations
     */
    public function optimize(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Generate suggestions for the next 90 days
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(90);

        // Generate monthly suggestions
        $suggestions = [];
        $currentStart = $startDate->copy();

        while ($currentStart->lt($endDate)) {
            $currentEnd = min($currentStart->copy()->addDays(30), $endDate);

            $suggestion = $this->pricingService->generatePriceSuggestion(
                $property,
                $currentStart,
                $currentEnd
            );

            $suggestions[] = $suggestion;
            $currentStart = $currentEnd->copy()->addDay();
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing optimization completed',
            'data' => [
                'period' => [
                    'start' => $startDate->toDateString(),
                    'end' => $endDate->toDateString(),
                ],
                'suggestions' => $suggestions,
            ],
        ]);
    }

    /**
     * Batch accept high-confidence suggestions
     */
    public function batchAccept(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        // Check authorization
        if ($request->user()->id !== $property->user_id && ! $request->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $minConfidence = $request->input('min_confidence', 80);

        $suggestions = $property->priceSuggestions()
            ->pending()
            ->highConfidence($minConfidence)
            ->get();

        $acceptedCount = 0;
        foreach ($suggestions as $suggestion) {
            if (! $suggestion->isExpired()) {
                $suggestion->accept();
                $acceptedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$acceptedCount} high-confidence suggestions accepted",
            'data' => [
                'accepted_count' => $acceptedCount,
                'min_confidence' => $minConfidence,
            ],
        ]);
    }
}
