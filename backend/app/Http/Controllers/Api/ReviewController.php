<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\ReviewHelpfulVote;
use App\Models\ReviewResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display reviews for a property
     */
    public function index(Request $request)
    {
        // Create cache key from request parameters
        $cacheKey = 'reviews_' . md5(json_encode($request->all()));
        
        // Cache reviews for 10 minutes (they change less frequently than properties)
        $result = Cache::tags(['reviews'])->remember($cacheKey, 600, function () use ($request) {
            $query = Review::with(['user', 'booking', 'responses.user', 'helpfulVotes'])
                ->approved();

            // Filter by property
            if ($request->has('property_id')) {
                $query->where('property_id', $request->property_id);
            }

            // Filter by rating
            if ($request->has('min_rating')) {
                $query->where('rating', '>=', $request->min_rating);
            }

            if ($request->has('max_rating')) {
                $query->where('rating', '<=', $request->max_rating);
            }

            // Filter by verified guests only
            if ($request->boolean('verified_only')) {
                $query->verifiedGuest();
            }

            // Filter by has owner response
            if ($request->has('has_response')) {
                if ($request->boolean('has_response')) {
                    $query->withResponse();
                } else {
                    $query->withoutResponse();
                }
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            if ($sortBy === 'helpful') {
                $query->orderBy('helpful_count', $sortOrder);
            } elseif ($sortBy === 'rating') {
                $query->orderBy('rating', $sortOrder);
            } else {
                $query->orderBy('created_at', $sortOrder);
            }

            return $query->paginate($request->get('per_page', 15));
        });

        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get reviews for current user
     */
    public function myReviews(Request $request)
    {
        $reviews = Review::with(['property', 'booking', 'responses'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ]);
    }

    /**
     * Store a newly created review
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'property_id' => 'required|exists:properties,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'check_in_rating' => 'nullable|integer|min:1|max:5',
            'accuracy_rating' => 'nullable|integer|min:1|max:5',
            'location_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if property exists
        $property = Property::find($request->property_id);
        if (! $property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found',
            ], 404);
        }

        // Determine booking: require a completed booking for this property & user
        $bookingId = $request->booking_id;
        if ($bookingId) {
            $booking = Booking::where('id', $bookingId)
                ->where('property_id', $request->property_id)
                ->where('user_id', $request->user()->id)
                ->first();
            if (! $booking || $booking->status !== 'completed') {
                return response()->json([
                    'message' => 'You must complete the booking before reviewing this property.',
                ], 422);
            }
        } else {
            $booking = Booking::where('property_id', $request->property_id)
                ->where('user_id', $request->user()->id)
                ->where('status', 'completed')
                ->latest()
                ->first();
            if (! $booking) {
                return response()->json([
                    'message' => 'You must have a completed booking for this property before reviewing.',
                ], 422);
            }
            $bookingId = $booking->id;
        }

        // Prevent multiple reviews for same property by same user (test expectation)
        $existingPropertyReview = Review::where('property_id', $request->property_id)
            ->where('user_id', $request->user()->id)
            ->first();
        if ($existingPropertyReview) {
            return response()->json([
                'message' => 'You have already reviewed this property.',
            ], 422);
        }

        // Handle photo uploads
        $photos = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews', 'public');
                $photos[] = Storage::url($path);
            }
        }

        // Create review
        $review = Review::create([
            'property_id' => $request->property_id,
            'booking_id' => $bookingId,
            'user_id' => $request->user()->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'cleanliness_rating' => $request->cleanliness_rating,
            'communication_rating' => $request->communication_rating,
            'check_in_rating' => $request->check_in_rating,
            'accuracy_rating' => $request->accuracy_rating,
            'location_rating' => $request->location_rating,
            'value_rating' => $request->value_rating,
            'photos' => $photos,
            'is_approved' => true,
        ]);

        $review->load(['user', 'property']);

        // Return flat structure expected by tests
        return response()->json([
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ], 201);
    }

    /**
     * Display the specified review
     */
    public function show($id)
    {
        $review = Review::with(['user', 'property', 'booking', 'responses.user', 'helpfulVotes'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $review,
        ]);
    }

    /**
     * Update the specified review
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check authorization
        if (! $review->canBeEditedBy($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to edit this review',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'cleanliness_rating' => 'nullable|integer|min:1|max:5',
            'communication_rating' => 'nullable|integer|min:1|max:5',
            'check_in_rating' => 'nullable|integer|min:1|max:5',
            'accuracy_rating' => 'nullable|integer|min:1|max:5',
            'location_rating' => 'nullable|integer|min:1|max:5',
            'value_rating' => 'nullable|integer|min:1|max:5',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Handle new photo uploads
        $photos = $review->photos ?? [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store('reviews', 'public');
                $photos[] = Storage::url($path);
            }
        }

        // Update review
        $review->update([
            'rating' => $request->rating,
            'comment' => $request->comment,
            'cleanliness_rating' => $request->cleanliness_rating,
            'communication_rating' => $request->communication_rating,
            'check_in_rating' => $request->check_in_rating,
            'accuracy_rating' => $request->accuracy_rating,
            'location_rating' => $request->location_rating,
            'value_rating' => $request->value_rating,
            'photos' => $photos,
        ]);

        $review->load(['user', 'property']);

        return response()->json([
            'id' => $review->id,
            'rating' => $review->rating,
            'comment' => $review->comment,
        ]);
    }

    /**
     * Remove the specified review
     */
    public function destroy(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check authorization
        if (! $review->canBeDeletedBy($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this review',
            ], 403);
        }

        // Delete photos
        if ($review->photos) {
            foreach ($review->photos as $photo) {
                $path = str_replace('/storage/', '', $photo);
                Storage::disk('public')->delete($path);
            }
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully',
        ]);
    }

    /**
     * Add owner response to review
     */
    public function addResponse(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Check authorization
        if (! $review->canBeRespondedBy($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to respond to this review',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'response' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create response
        $response = ReviewResponse::create([
            'review_id' => $review->id,
            'user_id' => $request->user()->id,
            'response' => $request->response,
        ]);

        // Update review with latest response info
        $review->update([
            'owner_response' => $request->response,
            'owner_response_at' => now(),
        ]);

        $response->load('user');

        return response()->json([
            'success' => true,
            'data' => [
                'review_id' => $review->id,
                'response' => $response->response,
            ],
        ]);
    }

    /**
     * Vote review as helpful/not helpful
     */
    public function vote(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        // Default to helpful if not provided (test posts without body)
        $isHelpful = $request->has('is_helpful') ? (bool) $request->is_helpful : true;

        // Check if user already voted
        $existingVote = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingVote) {
            $existingVote->update(['is_helpful' => $isHelpful]);
        } else {
            ReviewHelpfulVote::create([
                'review_id' => $review->id,
                'user_id' => $request->user()->id,
                'is_helpful' => $isHelpful,
            ]);
        }

        // Update helpful count
        $helpfulCount = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('is_helpful', true)
            ->count();

        $review->update(['helpful_count' => $helpfulCount]);

        return response()->json([
            'helpful_count' => $helpfulCount,
        ]);
    }

    /**
     * Get property average rating
     */
    public function propertyRating($propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $reviews = Review::where('property_id', $propertyId)
            ->approved()
            ->get();

        $averageRating = $reviews->avg('rating') ?? 0;
        $totalReviews = $reviews->count();

        $ratingBreakdown = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];

        $categoryAverages = [
            'cleanliness' => $reviews->avg('cleanliness_rating') ?? 0,
            'communication' => $reviews->avg('communication_rating') ?? 0,
            'check_in' => $reviews->avg('check_in_rating') ?? 0,
            'accuracy' => $reviews->avg('accuracy_rating') ?? 0,
            'location' => $reviews->avg('location_rating') ?? 0,
            'value' => $reviews->avg('value_rating') ?? 0,
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'average_rating' => round($averageRating, 2),
                'total_reviews' => $totalReviews,
                'rating_breakdown' => $ratingBreakdown,
                'category_averages' => $categoryAverages,
            ],
        ]);
    }

    /**
     * List reviews for a property (public endpoint alias required by tests)
     */
    public function propertyReviews(Property $property)
    {
        $reviews = Review::where('property_id', $property->id)
            ->approved()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $reviews,
        ]);
    }
}

