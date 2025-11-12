<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ConciergeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConciergeServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ConciergeService::with('serviceProvider')
            ->available();

        if ($request->has('service_type')) {
            $query->byType($request->service_type);
        }

        if ($request->has('max_price')) {
            $query->where('base_price', '<=', $request->max_price);
        }

        if ($request->has('max_guests')) {
            $query->where('max_guests', '>=', $request->max_guests);
        }

        $services = $query->orderBy('service_type')
            ->orderBy('name')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }

    public function show(ConciergeService $service): JsonResponse
    {
        $service->load('serviceProvider');

        return response()->json([
            'success' => true,
            'data' => $service,
        ]);
    }

    public function types(): JsonResponse
    {
        $types = [
            [
                'value' => 'airport_pickup',
                'label' => 'Airport Pickup',
                'icon' => '✈️',
                'description' => 'Professional airport transfer service',
            ],
            [
                'value' => 'grocery_delivery',
                'label' => 'Grocery Delivery',
                'icon' => '🛒',
                'description' => 'Fresh groceries delivered to your door',
            ],
            [
                'value' => 'local_experience',
                'label' => 'Local Experiences',
                'icon' => '🎭',
                'description' => 'Curated tours and local activities',
            ],
            [
                'value' => 'personal_chef',
                'label' => 'Personal Chef',
                'icon' => '👨‍🍳',
                'description' => 'Private chef for in-home dining',
            ],
            [
                'value' => 'spa_service',
                'label' => 'Spa Services',
                'icon' => '💆',
                'description' => 'Relaxing spa treatments at your property',
            ],
            [
                'value' => 'car_rental',
                'label' => 'Car Rental',
                'icon' => '🚗',
                'description' => 'Convenient vehicle rental services',
            ],
            [
                'value' => 'babysitting',
                'label' => 'Babysitting',
                'icon' => '👶',
                'description' => 'Professional childcare services',
            ],
            [
                'value' => 'housekeeping',
                'label' => 'Housekeeping',
                'icon' => '🧹',
                'description' => 'Daily housekeeping and laundry',
            ],
            [
                'value' => 'pet_care',
                'label' => 'Pet Care',
                'icon' => '🐕',
                'description' => 'Pet sitting and walking services',
            ],
            [
                'value' => 'other',
                'label' => 'Other Services',
                'icon' => '⭐',
                'description' => 'Additional concierge services',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }

    public function featured(): JsonResponse
    {
        $services = ConciergeService::with('serviceProvider')
            ->available()
            ->inRandomOrder()
            ->limit(6)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $services,
        ]);
    }
}

