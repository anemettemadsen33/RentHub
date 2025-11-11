<?php

namespace App\\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Services\GeoSearchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MapSearchController extends Controller
{
    /**
     * Search properties within radius
     *
     * @group Map Search
     *
     * @bodyParam latitude float required Center latitude. Example: 44.4268
     * @bodyParam longitude float required Center longitude. Example: 26.1025
     * @bodyParam radius float required Radius in kilometers. Example: 10
     * @bodyParam type string Property type filter. Example: apartment
     * @bodyParam min_price float Minimum price per night. Example: 50
     * @bodyParam max_price float Maximum price per night. Example: 200
     * @bodyParam bedrooms int Minimum bedrooms. Example: 2
     * @bodyParam bathrooms int Minimum bathrooms. Example: 1
     * @bodyParam guests int Minimum guests capacity. Example: 4
     * @bodyParam amenities array Amenity IDs. Example: [1,2,3]
     */
    public function searchRadius(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'required|numeric|min:0.1|max:100',
            'type' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'guests' => 'nullable|integer|min:1',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $request->only([
            'type', 'min_price', 'max_price',
            'bedrooms', 'bathrooms', 'guests', 'amenities',
        ]);

        $properties = GeoSearchService::searchWithinRadius(
            $request->latitude,
            $request->longitude,
            $request->radius,
            $filters
        );

        return response()->json([
            'success' => true,
            'data' => [
                'properties' => $properties,
                'count' => $properties->count(),
                'center' => [
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                ],
                'radius' => $request->radius,
            ],
        ]);
    }

    /**
     * Search properties within map bounds
     *
     * @group Map Search
     *
     * @bodyParam sw_lat float required Southwest corner latitude. Example: 44.3968
     * @bodyParam sw_lng float required Southwest corner longitude. Example: 26.0725
     * @bodyParam ne_lat float required Northeast corner latitude. Example: 44.4568
     * @bodyParam ne_lng float required Northeast corner longitude. Example: 26.1325
     * @bodyParam type string Property type filter. Example: apartment
     * @bodyParam min_price float Minimum price per night. Example: 50
     * @bodyParam max_price float Maximum price per night. Example: 200
     * @bodyParam bedrooms int Minimum bedrooms. Example: 2
     * @bodyParam bathrooms int Minimum bathrooms. Example: 1
     * @bodyParam guests int Minimum guests capacity. Example: 4
     * @bodyParam amenities array Amenity IDs. Example: [1,2,3]
     * @bodyParam zoom int Map zoom level for clustering. Example: 12
     */
    public function searchBounds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sw_lat' => 'required|numeric|between:-90,90',
            'sw_lng' => 'required|numeric|between:-180,180',
            'ne_lat' => 'required|numeric|between:-90,90',
            'ne_lng' => 'required|numeric|between:-180,180',
            'type' => 'nullable|string',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0',
            'bedrooms' => 'nullable|integer|min:0',
            'bathrooms' => 'nullable|integer|min:0',
            'guests' => 'nullable|integer|min:1',
            'amenities' => 'nullable|array',
            'amenities.*' => 'integer|exists:amenities,id',
            'zoom' => 'nullable|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $filters = $request->only([
            'type', 'min_price', 'max_price',
            'bedrooms', 'bathrooms', 'guests', 'amenities',
        ]);

        $properties = GeoSearchService::searchWithinBounds(
            $request->sw_lat,
            $request->sw_lng,
            $request->ne_lat,
            $request->ne_lng,
            $filters
        );

        // Cluster properties if zoom level provided
        $zoom = $request->input('zoom', 12);
        $markers = GeoSearchService::clusterProperties($properties, $zoom);

        return response()->json([
            'success' => true,
            'data' => [
                'markers' => $markers,
                'count' => $properties->count(),
                'bounds' => [
                    'southwest' => [
                        'lat' => $request->sw_lat,
                        'lng' => $request->sw_lng,
                    ],
                    'northeast' => [
                        'lat' => $request->ne_lat,
                        'lng' => $request->ne_lng,
                    ],
                ],
            ],
        ]);
    }

    /**
     * Get property details for map popup
     *
     * @group Map Search
     */
    public function getPropertyMapData($id)
    {
        $property = \App\Models\Property::with(['user', 'amenities'])
            ->where('is_active', true)
            ->where('status', 'published')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $property->id,
                'title' => $property->title,
                'type' => $property->type,
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'guests' => $property->guests,
                'price_per_night' => $property->price_per_night,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'address' => [
                    'street' => $property->street_address,
                    'city' => $property->city,
                    'state' => $property->state,
                    'country' => $property->country,
                ],
                'images' => [
                    'main' => $property->main_image,
                    'all' => $property->images,
                ],
                'rating' => $property->reviews()->avg('rating'),
                'reviews_count' => $property->reviews()->count(),
            ],
        ]);
    }

    /**
     * Geocode address to coordinates
     *
     * @group Map Search
     *
     * @bodyParam address string required Full address. Example: "Strada Aviatorilor 10, București, România"
     */
    public function geocode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // You can integrate with Google Geocoding API, Mapbox, or OpenStreetMap Nominatim
        // For now, return a placeholder response
        return response()->json([
            'success' => true,
            'message' => 'Geocoding endpoint - integrate with your preferred geocoding service',
            'data' => null,
        ]);
    }
}

