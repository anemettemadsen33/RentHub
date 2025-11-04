<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\ReviewHelpfulVote;
use App\Models\ReviewResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display reviews for a property
     */
    public function index(Request $request)
    {
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

        $reviews = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $reviews,
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

        // Check if user has already reviewed this property/booking
        if ($request->booking_id) {
            $existingReview = Review::where('booking_id', $request->booking_id)
                ->where('user_id', $request->user()->id)
                ->first();

            if ($existingReview) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already reviewed this booking',
                ], 422);
            }
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
            'booking_id' => $request->booking_id,
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
            'is_approved' => true, // Auto-approve (can be changed based on requirements)
        ]);

        $review->load(['user', 'property']);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully',
            'data' => $review,
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
            'success' => true,
            'message' => 'Review updated successfully',
            'data' => $review,
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
            'message' => 'Response added successfully',
            'data' => $response,
        ], 201);
    }

    /**
     * Vote review as helpful/not helpful
     */
    public function vote(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'is_helpful' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if user already voted
        $existingVote = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existingVote) {
            // Update existing vote
            $existingVote->update([
                'is_helpful' => $request->is_helpful,
            ]);
        } else {
            // Create new vote
            ReviewHelpfulVote::create([
                'review_id' => $review->id,
                'user_id' => $request->user()->id,
                'is_helpful' => $request->is_helpful,
            ]);
        }

        // Update helpful count
        $helpfulCount = ReviewHelpfulVote::where('review_id', $review->id)
            ->where('is_helpful', true)
            ->count();

        $review->update(['helpful_count' => $helpfulCount]);

        return response()->json([
            'success' => true,
            'message' => 'Vote recorded successfully',
            'data' => [
                'helpful_count' => $helpfulCount,
            ],
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
}
