<?php

namespace App\\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PropertyRecommendation;
use App\Models\UserBehavior;
use App\Services\AI\RecommendationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AIRecommendationController extends Controller
{
    public function __construct(
        private RecommendationService $recommendationService
    ) {}

    /**
     * Get personalized property recommendations
     */
    public function getRecommendations(Request $request): JsonResponse
    {
        $userId = auth()->id();

        $recommendations = $this->recommendationService->generateRecommendations($userId);

        // Mark as shown
        PropertyRecommendation::where('user_id', $userId)
            ->whereIn('property_id', $recommendations->pluck('property.id'))
            ->update(['shown' => true]);

        return response()->json([
            'success' => true,
            'data' => $recommendations->map(function ($rec) {
                return [
                    'property' => $rec['property'],
                    'score' => $rec['score'],
                    'reasons' => $rec['reasons'] ?? [],
                ];
            }),
        ]);
    }

    /**
     * Track recommendation click
     */
    public function trackClick(Request $request, int $propertyId): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $userId = auth()->id();

        $recommendation = PropertyRecommendation::where('user_id', $userId)
            ->where('property_id', $propertyId)
            ->first();

        if ($recommendation) {
            $recommendation->markClicked();
        }

        // Track user behavior
        UserBehavior::track($userId, 'recommendation_click', $propertyId);

        return response()->json([
            'success' => true,
            'message' => 'Click tracked successfully',
        ]);
    }

    /**
     * Track property view
     */
    public function trackView(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
            'duration' => 'nullable|integer', // seconds
        ]);

        $userId = auth()->id();

        UserBehavior::track($userId, 'view', $request->property_id, [
            'duration' => $request->duration,
            'source' => $request->source ?? 'search',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'View tracked successfully',
        ]);
    }

    /**
     * Track search behavior
     */
    public function trackSearch(Request $request): JsonResponse
    {
        $request->validate([
            'search_criteria' => 'required|array',
        ]);

        $userId = auth()->id();

        UserBehavior::track($userId, 'search', null, [
            'criteria' => $request->search_criteria,
            'results_count' => $request->results_count ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Search tracked successfully',
        ]);
    }

    /**
     * Get similar properties
     */
    public function getSimilarProperties(int $propertyId): JsonResponse
    {
        $property = \App\Models\Property::findOrFail($propertyId);

        $similar = \App\Models\SimilarProperty::where('property_id', $propertyId)
            ->with('similarProperty')
            ->orderByDesc('similarity_score')
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $similar->map(function ($item) {
                return [
                    'property' => $item->similarProperty,
                    'similarity_score' => $item->similarity_score,
                    'similarity_factors' => $item->similarity_factors,
                ];
            }),
        ]);
    }
}

