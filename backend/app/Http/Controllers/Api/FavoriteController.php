<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get user's favorites
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with(['property:id,title,price_per_night,city,country,status'])
            ->get()
            ->map(function ($favorite) {
                return [
                    'id' => $favorite->id,
                    'property_id' => $favorite->property_id,
                    'property' => $favorite->property,
                    'added_at' => $favorite->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }

    /**
     * Add property to favorites
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,id',
        ]);

        $favorite = Favorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'property_id' => $request->property_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Property added to favorites',
            'data' => $favorite,
        ], 201);
    }

    /**
     * Remove property from favorites
     */
    public function destroy(Request $request, int $propertyId): JsonResponse
    {
        $deleted = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Favorite not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property removed from favorites',
        ]);
    }

    /**
     * Check if property is favorited
     */
    public function check(Request $request, int $propertyId): JsonResponse
    {
        $isFavorite = Favorite::where('user_id', $request->user()->id)
            ->where('property_id', $propertyId)
            ->exists();

        return response()->json([
            'success' => true,
            'is_favorite' => $isFavorite,
        ]);
    }
}

