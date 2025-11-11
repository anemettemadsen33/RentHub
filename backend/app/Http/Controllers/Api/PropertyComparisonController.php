<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PropertyComparisonController extends Controller
{
    /**
     * Get properties in comparison list
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id ?? 'guest_'.$request->ip();
        $cacheKey = "property_comparison:{$userId}";

        $propertyIds = Cache::get($cacheKey, []);

        $properties = Property::with(['reviews', 'amenities'])
            ->whereIn('id', $propertyIds)
            ->where('status', 'available')
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'description' => $property->description,
                    'price' => $property->price_per_night,
                    'price_per_night' => $property->price_per_night,
                    'currency' => $property->currency ?? 'USD',
                    'type' => $property->type,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'max_guests' => $property->max_guests,
                    'area' => $property->area,
                    'address' => $property->address,
                    'city' => $property->city,
                    'country' => $property->country,
                    'status' => $property->status,
                    'rating' => $property->rating,
                    'review_count' => $property->reviews_count ?? 0,
                    'image_url' => $property->image_url ?? $property->images[0] ?? null,
                    'amenities' => $property->amenities->pluck('name')->toArray(),
                    'features' => $property->features ?? [],
                    'instant_book' => $property->instant_book ?? false,
                ];
            });

        return response()->json([
            'properties' => $properties,
            'count' => $properties->count(),
        ]);
    }

    /**
     * Add property to comparison
     */
    public function add(Request $request, $propertyId)
    {
        $request->validate([
            'property_id' => 'sometimes|exists:properties,id',
        ]);

        $userId = $request->user()->id ?? 'guest_'.$request->ip();
        $cacheKey = "property_comparison:{$userId}";

        $propertyIds = Cache::get($cacheKey, []);

        // Limit to 4 properties
        if (count($propertyIds) >= 4) {
            return response()->json([
                'message' => 'Maximum 4 properties can be compared at once',
            ], 400);
        }

        // Add if not already in list
        if (! in_array($propertyId, $propertyIds)) {
            $propertyIds[] = (int) $propertyId;
            Cache::put($cacheKey, $propertyIds, now()->addDays(7));
        }

        return response()->json([
            'message' => 'Property added to comparison',
            'count' => count($propertyIds),
        ]);
    }

    /**
     * Remove property from comparison
     */
    public function remove(Request $request, $propertyId)
    {
        $userId = $request->user()->id ?? 'guest_'.$request->ip();
        $cacheKey = "property_comparison:{$userId}";

        $propertyIds = Cache::get($cacheKey, []);
        $propertyIds = array_values(array_filter($propertyIds, fn ($id) => $id != $propertyId));

        Cache::put($cacheKey, $propertyIds, now()->addDays(7));

        return response()->json([
            'message' => 'Property removed from comparison',
            'count' => count($propertyIds),
        ]);
    }

    /**
     * Clear all properties from comparison
     */
    public function clear(Request $request)
    {
        $userId = $request->user()->id ?? 'guest_'.$request->ip();
        $cacheKey = "property_comparison:{$userId}";

        Cache::forget($cacheKey);

        return response()->json([
            'message' => 'Comparison cleared',
        ]);
    }

    /**
     * Get comparison count
     */
    public function count(Request $request)
    {
        $userId = $request->user()->id ?? 'guest_'.$request->ip();
        $cacheKey = "property_comparison:{$userId}";

        $propertyIds = Cache::get($cacheKey, []);

        return response()->json([
            'count' => count($propertyIds),
        ]);
    }
}

