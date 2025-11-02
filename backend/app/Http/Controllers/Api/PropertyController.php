<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Property::with(['amenities', 'user:id,name,email'])
            ->active()
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating');

        // Search filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->filled('city')) {
            $query->byLocation($request->get('city'));
        }

        if ($request->filled('country')) {
            $query->byLocation(null, $request->get('country'));
        }

        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->priceBetween($request->get('min_price'), $request->get('max_price'));
        }

        if ($request->filled('guests')) {
            $query->where('guests', '>=', $request->get('guests'));
        }

        if ($request->filled('bedrooms')) {
            $query->where('bedrooms', '>=', $request->get('bedrooms'));
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $query->available($request->get('check_in'), $request->get('check_out'));
        }

        if ($request->filled('amenities')) {
            $amenityIds = explode(',', $request->get('amenities'));
            $query->whereHas('amenities', function ($q) use ($amenityIds) {
                $q->whereIn('amenities.id', $amenityIds);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if ($sortBy === 'price') {
            $query->orderBy('price_per_night', $sortOrder);
        } elseif ($sortBy === 'rating') {
            $query->orderBy('average_rating', $sortOrder);
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $properties = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    public function show(Property $property): JsonResponse
    {
        $property->load([
            'amenities:id,name,icon,category',
            'user:id,name,email,phone,bio,avatar',
            'reviews' => function ($query) {
                $query->where('is_approved', true)
                      ->with('user:id,name,avatar')
                      ->orderBy('created_at', 'desc')
                      ->take(10);
            }
        ]);

        $property->loadCount('reviews');
        $property->loadAvg('reviews as average_rating', 'rating');

        return response()->json([
            'success' => true,
            'data' => $property,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type' => 'required|string',
            'bedrooms' => 'required|integer|min:1',
            'bathrooms' => 'required|integer|min:1',
            'guests' => 'required|integer|min:1',
            'price_per_night' => 'required|numeric|min:0',
            'street_address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'postal_code' => 'required|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $property = auth()->user()->properties()->create($request->all());
        
        if ($request->filled('amenities')) {
            $property->amenities()->attach($request->get('amenities'));
        }

        $property->load('amenities', 'user');

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property,
        ], 201);
    }

    public function update(Request $request, Property $property): JsonResponse
    {
        $this->authorize('update', $property);

        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'bedrooms' => 'sometimes|required|integer|min:1',
            'bathrooms' => 'sometimes|required|integer|min:1',
            'guests' => 'sometimes|required|integer|min:1',
            'price_per_night' => 'sometimes|required|numeric|min:0',
            'amenities' => 'array',
            'amenities.*' => 'exists:amenities,id',
        ]);

        $property->update($request->except(['amenities']));

        if ($request->filled('amenities')) {
            $property->amenities()->sync($request->get('amenities'));
        }

        $property->load('amenities', 'user');

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => $property,
        ]);
    }

    public function destroy(Property $property): JsonResponse
    {
        $this->authorize('delete', $property);

        $property->delete();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully',
        ]);
    }

    public function featured(): JsonResponse
    {
        $properties = Property::with(['amenities:id,name,icon', 'user:id,name'])
            ->active()
            ->featured()
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating')
            ->take(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'location' => 'nullable|string',
            'check_in' => 'nullable|date|after:today',
            'check_out' => 'nullable|date|after:check_in',
            'guests' => 'nullable|integer|min:1',
        ]);

        $query = Property::with(['amenities:id,name,icon'])
            ->active()
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating');

        if ($request->filled('location')) {
            $location = $request->get('location');
            $query->where(function ($q) use ($location) {
                $q->where('city', 'like', "%{$location}%")
                  ->orWhere('country', 'like', "%{$location}%")
                  ->orWhere('state', 'like', "%{$location}%");
            });
        }

        if ($request->filled('guests')) {
            $query->where('guests', '>=', $request->get('guests'));
        }

        if ($request->filled('check_in') && $request->filled('check_out')) {
            $query->available($request->get('check_in'), $request->get('check_out'));
        }

        $properties = $query->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }
}
