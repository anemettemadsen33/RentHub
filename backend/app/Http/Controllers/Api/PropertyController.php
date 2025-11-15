<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePropertyRequest;
use App\Http\Requests\UpdatePropertyRequest;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // Create cache key from request parameters
        $cacheKey = 'properties_' . md5(json_encode($request->all()));
        
        // Cache search results for 5 minutes (300 seconds)
        $cacheTTL = 300;
        
        // For simple listing without filters, cache longer (30 minutes)
        if (!$request->hasAny(['search', 'city', 'country', 'min_price', 'max_price', 'check_in', 'check_out', 'amenities'])) {
            $cacheTTL = 1800;
        }
        
        $result = Cache::remember($cacheKey, $cacheTTL, function () use ($request) {
            $query = Property::with(['amenities', 'user:id,name,email'])
                ->where('status', 'available')
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

            // Filter by type
            if ($request->filled('type')) {
                $query->where('type', $request->get('type'));
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
            // Support legacy param names `sort` and `order`
            $sortBy = $request->get('sort_by', $request->get('sort', 'created_at'));
            $sortOrder = $request->get('sort_order', $request->get('order', 'desc'));

            if ($sortBy === 'price') {
                $query->orderBy('price_per_night', $sortOrder);
            } elseif ($sortBy === 'rating') {
                $query->orderBy('average_rating', $sortOrder);
            } else {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination (return only items array for legacy tests)
            $perPage = min($request->get('per_page', 15), 50);
            return $query->paginate($perPage);
        });

        return response()->json([
            'success' => true,
            'data' => $result->items(),
        ]);
    }

    public function show(Property $property): JsonResponse
    {
        // Cache individual property for 30 minutes
        $cacheKey = "property_{$property->id}";
        
        $property = Cache::remember($cacheKey, 1800, function () use ($property) {
            $property->load([
                'amenities:id,name,icon,category',
                'user:id,name,email,phone,bio,avatar',
                'reviews' => function ($query) {
                    $query->where('is_approved', true)
                        ->with('user:id,name,avatar')
                        ->orderBy('created_at', 'desc')
                        ->take(10);
                },
            ]);

            $property->loadCount('reviews');
            $property->loadAvg('reviews as average_rating', 'rating');

            // Ensure numeric float for average_rating as expected by tests (not string)
            if (isset($property->average_rating)) {
                $property->average_rating = round((float) $property->average_rating, 1);
            } else {
                $property->average_rating = 0.0;
            }
            
            return $property;
        });

        return response()->json([
            'success' => true,
            'data' => $property,
        ]);
    }

    public function store(StorePropertyRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Backwards compatibility: tests may send 'price' instead of 'price_per_night'
        if (isset($data['price']) && ! isset($data['price_per_night'])) {
            $data['price_per_night'] = $data['price'];
        }
        if (isset($data['price_per_night']) && ! isset($data['price'])) {
            $data['price'] = $data['price_per_night'];
        }

        // Set default values
        $data['user_id'] = auth()->id();
        // Maintain owner_id for tests expecting this column
        $data['owner_id'] = auth()->id();
        // Align with enum: available/booked/maintenance
        $data['status'] = $data['status'] ?? 'available';
        // Consider available as active by default
        $data['is_active'] = ($data['status'] ?? 'available') === 'available';

        // Create property
        $property = Property::create($data);

        // Attach amenities if provided
        if ($request->filled('amenities')) {
            $property->amenities()->attach($request->amenities);
        }

        $property->load(['amenities', 'user:id,name,email,avatar']);

        // Invalidate property cache
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => $property,
        ], 201);
    }

    public function update(UpdatePropertyRequest $request, Property $property): JsonResponse
    {
        $data = $request->validated();

        // Backwards compatibility for price field
        if (isset($data['price']) && ! isset($data['price_per_night'])) {
            $data['price_per_night'] = $data['price'];
        }
        if (isset($data['price_per_night']) && ! isset($data['price'])) {
            $data['price'] = $data['price_per_night'];
        }

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

        // Invalidate property cache
        Cache::flush();

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

        // Invalidate property cache
        Cache::flush();

        return response()->json([
            'success' => true,
            'message' => 'Property deleted successfully',
        ]);
    }

    public function featured(): JsonResponse
    {
        try {
            $properties = Property::select(['id', 'title', 'price_per_night', 'status', 'user_id', 'is_featured', 'street_address', 'city', 'country'])
                ->where('status', 'available')
                ->where('is_featured', true)
                ->with(['images' => function ($query) {
                    $query->where('is_primary', true)->select('id', 'property_id', 'image_path', 'is_primary');
                }])
                ->take(8)
                ->get()
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'title' => $property->title,
                        'price_per_night' => (float) $property->price_per_night,
                        'status' => $property->status,
                        'location' => $property->city.', '.$property->country,
                        'image' => $property->images->first()?->image_path,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $properties,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch featured properties: '.$e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'location' => 'nullable|string',
                'check_in' => 'nullable|date',
                'check_out' => 'nullable|date|after:check_in',
                'guests' => 'nullable|integer|min:1',
                'min_price' => 'nullable|numeric|min:0',
                'max_price' => 'nullable|numeric|min:0',
                'bedrooms' => 'nullable|integer|min:0',
            ]);

            $query = Property::query()
                ->where('status', 'available');

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

            if ($request->filled('min_price')) {
                $query->where('price_per_night', '>=', $request->get('min_price'));
            }

            if ($request->filled('max_price')) {
                $query->where('price_per_night', '<=', $request->get('max_price'));
            }

            if ($request->filled('bedrooms')) {
                $query->where('bedrooms', '>=', $request->get('bedrooms'));
            }

            $properties = $query->limit(12)->get();

            return response()->json([
                'success' => true,
                'data' => $properties,
            ]);
        } catch (\Exception $e) {
            \Log::error('Property search failed: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed: '.$e->getMessage(),
            ], 500);
        }
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Validate required fields for publishing
        if (! $property->title || ! $property->description || ! $property->price_per_night) {
            return response()->json([
                'success' => false,
                'message' => 'Property must have title, description and price to be published',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $images = $property->images ?? [];

        if (! isset($images[$imageIndex])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
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
        if ($property->user_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'image_index' => 'required|integer|min:0',
        ]);

        $images = $property->images ?? [];

        if (! isset($images[$request->image_index])) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found',
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

