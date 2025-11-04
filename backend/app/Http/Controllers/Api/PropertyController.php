<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $data = $request->validated();
        
        // Set default values
        $data['user_id'] = auth()->id();
        $data['status'] = $data['status'] ?? 'draft';
        $data['is_active'] = ($data['status'] ?? 'draft') === 'published';
        
        // Create property
        $property = Property::create($data);
        
        // Attach amenities if provided
        if ($request->filled('amenities')) {
            $property->amenities()->attach($request->amenities);
        }

        $property->load(['amenities', 'user:id,name,email,avatar']);

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property,
        ], 201);
    }

    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        $data = $request->validated();
        
        // Update is_active based on status
        if (isset($data['status'])) {
            $data['is_active'] = $data['status'] === 'published';
        }
        
        $property->update($data);

        // Sync amenities if provided
        if ($request->filled('amenities')) {
            $property->amenities()->sync($request->amenities);
        }

        $property->load(['amenities', 'user:id,name,email,avatar']);

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

    /**
     * Get owner's properties
     */
    public function myProperties(Request $request): JsonResponse
    {
        $query = Property::with(['amenities:id,name,icon'])
            ->where('user_id', auth()->id())
            ->withCount('bookings')
            ->withCount('reviews')
            ->withAvg('reviews as average_rating', 'rating');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $properties = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json([
            'success' => true,
            'data' => $properties,
        ]);
    }

    /**
     * Publish property
     */
    public function publish(Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // Validate required fields for publishing
        if (!$property->title || !$property->description || !$property->price_per_night) {
            return response()->json([
                'success' => false,
                'message' => 'Property must have title, description and price to be published'
            ], 422);
        }

        $property->publish();

        return response()->json([
            'success' => true,
            'message' => 'Property published successfully',
            'data' => $property,
        ]);
    }

    /**
     * Unpublish property
     */
    public function unpublish(Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $property->unpublish();

        return response()->json([
            'success' => true,
            'message' => 'Property unpublished successfully',
            'data' => $property,
        ]);
    }

    /**
     * Block dates
     */
    public function blockDates(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date|after_or_equal:today',
        ]);

        foreach ($request->dates as $date) {
            $property->blockDate($date);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dates blocked successfully',
            'data' => $property->fresh(),
        ]);
    }

    /**
     * Unblock dates
     */
    public function unblockDates(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'dates' => 'required|array',
            'dates.*' => 'required|date',
        ]);

        foreach ($request->dates as $date) {
            $property->unblockDate($date);
        }

        return response()->json([
            'success' => true,
            'message' => 'Dates unblocked successfully',
            'data' => $property->fresh(),
        ]);
    }

    /**
     * Set custom pricing for dates
     */
    public function setCustomPricing(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'pricing' => 'required|array',
            'pricing.*.date' => 'required|date|after_or_equal:today',
            'pricing.*.price' => 'required|numeric|min:1',
        ]);

        foreach ($request->pricing as $item) {
            $property->setCustomPrice($item['date'], $item['price']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Custom pricing set successfully',
            'data' => $property->fresh(),
        ]);
    }

    /**
     * Upload property images
     */
    public function uploadImages(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'required|image|mimes:jpeg,jpg,png,gif|max:5120',
        ]);

        $uploadedImages = [];

        foreach ($request->file('images') as $image) {
            $path = $image->store('properties', 'public');
            $uploadedImages[] = $path;
        }

        // Merge with existing images
        $existingImages = $property->images ?? [];
        $allImages = array_merge($existingImages, $uploadedImages);

        $property->update([
            'images' => $allImages,
            'main_image' => $property->main_image ?? $uploadedImages[0],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Images uploaded successfully',
            'data' => [
                'uploaded' => $uploadedImages,
                'property' => $property->fresh(),
            ],
        ]);
    }

    /**
     * Delete property image
     */
    public function deleteImage(Request $request, Property $property, $imageIndex): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $images = $property->images ?? [];

        if (!isset($images[$imageIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        $imagePath = $images[$imageIndex];

        // Delete from storage
        Storage::disk('public')->delete($imagePath);

        // Remove from array
        unset($images[$imageIndex]);
        $images = array_values($images);

        // Update main image if deleted
        $mainImage = $property->main_image;
        if ($mainImage === $imagePath) {
            $mainImage = $images[0] ?? null;
        }

        $property->update([
            'images' => $images,
            'main_image' => $mainImage,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
            'data' => $property->fresh(),
        ]);
    }

    /**
     * Set main image
     */
    public function setMainImage(Request $request, Property $property): JsonResponse
    {
        // Check authorization
        if ($property->user_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'image_index' => 'required|integer|min:0',
        ]);

        $images = $property->images ?? [];

        if (!isset($images[$request->image_index])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        $property->update([
            'main_image' => $images[$request->image_index],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Main image set successfully',
            'data' => $property->fresh(),
        ]);
    }
}
