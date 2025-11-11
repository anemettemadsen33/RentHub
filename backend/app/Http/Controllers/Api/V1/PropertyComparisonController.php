<?php

namespace App\\Http\\Controllers\\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyComparison;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PropertyComparisonController extends Controller
{
    /**
     * Get user's comparison list
     */
    public function index(Request $request)
    {
        $sessionId = $request->header('X-Session-Id');

        if ($request->user()) {
            $comparison = PropertyComparison::where('user_id', $request->user()->id)
                ->latest()
                ->first();
        } elseif ($sessionId) {
            $comparison = PropertyComparison::where('session_id', $sessionId)
                ->latest()
                ->first();
        } else {
            return response()->json([
                'property_ids' => [],
                'properties' => [],
            ]);
        }

        if (! $comparison) {
            return response()->json([
                'property_ids' => [],
                'properties' => [],
            ]);
        }

        $propertyIds = $comparison->property_ids ?? [];
        $properties = Property::whereIn('id', $propertyIds)
            ->with(['user', 'amenities', 'reviews'])
            ->get()
            ->map(function ($property) {
                return [
                    'id' => $property->id,
                    'title' => $property->title,
                    'type' => $property->type,
                    'price_per_night' => $property->price_per_night,
                    'price_per_month' => $property->price_per_month,
                    'bedrooms' => $property->bedrooms,
                    'bathrooms' => $property->bathrooms,
                    'guests' => $property->guests,
                    'area_sqm' => $property->area_sqm,
                    'square_footage' => $property->square_footage,
                    'city' => $property->city,
                    'country' => $property->country,
                    'images' => $property->images,
                    'amenities' => $property->amenities->pluck('name'),
                    'average_rating' => round($property->reviews->avg('rating'), 1),
                    'review_count' => $property->reviews->count(),
                    'parking_available' => $property->parking_available,
                    'parking_spaces' => $property->parking_spaces,
                    'cleaning_fee' => $property->cleaning_fee,
                    'security_deposit' => $property->security_deposit,
                    'min_nights' => $property->min_nights,
                    'max_nights' => $property->max_nights,
                    'cancellation_policy' => $property->cancellation_policy,
                    'owner' => [
                        'id' => $property->user->id,
                        'name' => $property->user->name,
                        'avatar' => $property->user->avatar,
                    ],
                ];
            });

        return response()->json([
            'property_ids' => $propertyIds,
            'properties' => $properties,
            'count' => count($propertyIds),
        ]);
    }

    /**
     * Add property to comparison
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $propertyId = $request->property_id;
        $sessionId = $request->header('X-Session-Id') ?? Str::uuid()->toString();

        if ($request->user()) {
            $comparison = PropertyComparison::firstOrCreate(
                ['user_id' => $request->user()->id],
                ['property_ids' => [], 'expires_at' => now()->addDays(7)]
            );
        } else {
            $comparison = PropertyComparison::firstOrCreate(
                ['session_id' => $sessionId],
                ['property_ids' => [], 'expires_at' => now()->addDays(1)]
            );
        }

        $success = $comparison->addProperty($propertyId);

        if (! $success) {
            return response()->json([
                'message' => 'Maximum 4 properties can be compared at once',
            ], 400);
        }

        return response()->json([
            'message' => 'Property added to comparison',
            'property_ids' => $comparison->property_ids,
            'session_id' => $sessionId,
        ]);
    }

    /**
     * Remove property from comparison
     */
    public function remove(Request $request, $propertyId)
    {
        $sessionId = $request->header('X-Session-Id');

        if ($request->user()) {
            $comparison = PropertyComparison::where('user_id', $request->user()->id)->first();
        } elseif ($sessionId) {
            $comparison = PropertyComparison::where('session_id', $sessionId)->first();
        } else {
            return response()->json(['message' => 'Comparison not found'], 404);
        }

        if (! $comparison) {
            return response()->json(['message' => 'Comparison not found'], 404);
        }

        $comparison->removeProperty($propertyId);

        return response()->json([
            'message' => 'Property removed from comparison',
            'property_ids' => $comparison->property_ids,
        ]);
    }

    /**
     * Clear all comparisons
     */
    public function clear(Request $request)
    {
        $sessionId = $request->header('X-Session-Id');

        if ($request->user()) {
            PropertyComparison::where('user_id', $request->user()->id)->delete();
        } elseif ($sessionId) {
            PropertyComparison::where('session_id', $sessionId)->delete();
        }

        return response()->json([
            'message' => 'Comparison list cleared',
        ]);
    }

    /**
     * Get detailed comparison data
     */
    public function compare(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_ids' => 'required|array|min:2|max:4',
            'property_ids.*' => 'exists:properties,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $properties = Property::whereIn('id', $request->property_ids)
            ->with(['user', 'amenities', 'reviews'])
            ->get();

        $comparisonData = $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'description' => $property->description,
                'type' => $property->type,
                'furnishing_status' => $property->furnishing_status,

                // Pricing
                'price_per_night' => $property->price_per_night,
                'price_per_week' => $property->price_per_week,
                'price_per_month' => $property->price_per_month,
                'cleaning_fee' => $property->cleaning_fee,
                'security_deposit' => $property->security_deposit,

                // Property details
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'guests' => $property->guests,
                'area_sqm' => $property->area_sqm,
                'square_footage' => $property->square_footage,
                'built_year' => $property->built_year,
                'floor_number' => $property->floor_number,

                // Location
                'street_address' => $property->street_address,
                'city' => $property->city,
                'state' => $property->state,
                'country' => $property->country,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,

                // Amenities & Features
                'amenities' => $property->amenities->map(fn ($a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'icon' => $a->icon,
                ]),
                'parking_available' => $property->parking_available,
                'parking_spaces' => $property->parking_spaces,

                // Booking rules
                'min_nights' => $property->min_nights,
                'max_nights' => $property->max_nights,
                'cancellation_policy' => $property->cancellation_policy,
                'rules' => $property->rules,

                // Media
                'images' => $property->images,

                // Reviews
                'average_rating' => round($property->reviews->avg('rating'), 1),
                'review_count' => $property->reviews->count(),
                'rating_breakdown' => [
                    'cleanliness' => round($property->reviews->avg('cleanliness_rating'), 1),
                    'accuracy' => round($property->reviews->avg('accuracy_rating'), 1),
                    'communication' => round($property->reviews->avg('communication_rating'), 1),
                    'location' => round($property->reviews->avg('location_rating'), 1),
                    'checkin' => round($property->reviews->avg('checkin_rating'), 1),
                    'value' => round($property->reviews->avg('value_rating'), 1),
                ],

                // Owner
                'owner' => [
                    'id' => $property->user->id,
                    'name' => $property->user->name,
                    'avatar' => $property->user->avatar,
                    'joined_at' => $property->user->created_at->format('Y-m-d'),
                ],
            ];
        });

        return response()->json([
            'properties' => $comparisonData,
            'comparison_matrix' => $this->generateComparisonMatrix($comparisonData),
        ]);
    }

    private function generateComparisonMatrix($properties)
    {
        $features = [
            'price_per_night' => ['label' => 'Price per Night', 'type' => 'currency'],
            'price_per_month' => ['label' => 'Price per Month', 'type' => 'currency'],
            'bedrooms' => ['label' => 'Bedrooms', 'type' => 'number'],
            'bathrooms' => ['label' => 'Bathrooms', 'type' => 'number'],
            'guests' => ['label' => 'Max Guests', 'type' => 'number'],
            'area_sqm' => ['label' => 'Area (sqm)', 'type' => 'number'],
            'parking_available' => ['label' => 'Parking', 'type' => 'boolean'],
            'cleaning_fee' => ['label' => 'Cleaning Fee', 'type' => 'currency'],
            'security_deposit' => ['label' => 'Security Deposit', 'type' => 'currency'],
            'min_nights' => ['label' => 'Min Nights', 'type' => 'number'],
            'average_rating' => ['label' => 'Rating', 'type' => 'rating'],
            'review_count' => ['label' => 'Reviews', 'type' => 'number'],
        ];

        $matrix = [];
        foreach ($features as $key => $feature) {
            $row = [
                'feature' => $feature['label'],
                'type' => $feature['type'],
                'values' => [],
            ];

            foreach ($properties as $property) {
                $row['values'][] = $property[$key] ?? null;
            }

            $matrix[] = $row;
        }

        return $matrix;
    }
}

