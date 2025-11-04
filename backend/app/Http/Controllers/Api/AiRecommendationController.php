<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyRecommendation;
use App\Models\User;
use App\Models\UserBehavior;
use App\Models\Booking;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AiRecommendationController extends Controller
{
    /**
     * Get personalized property recommendations for a user
     */
    public function getRecommendations(Request $request)
    {
        $userId = $request->user()->id;
        $limit = $request->input('limit', 10);
        
        // Generate recommendations if not exists or expired
        $this->generateRecommendationsForUser($userId);
        
        // Get recommendations with property details
        $recommendations = PropertyRecommendation::with(['property.amenities', 'property.reviews'])
            ->where('user_id', $userId)
            ->where('valid_until', '>', now())
            ->orderBy('score', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($rec) {
                $rec->markShown();
                return [
                    'id' => $rec->id,
                    'property' => $rec->property,
                    'score' => $rec->score,
                    'reason' => $rec->reason,
                    'factors' => $rec->factors,
                    'recommendation_type' => $this->getRecommendationType($rec->factors),
                ];
            });
        
        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
            'generated_at' => now(),
        ]);
    }
    
    /**
     * Get similar properties based on a property
     */
    public function getSimilarProperties(Request $request, $propertyId)
    {
        $request->validate([
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);
        
        $property = Property::findOrFail($propertyId);
        $limit = $request->input('limit', 5);
        
        // Calculate similarity scores
        $similarProperties = $this->findSimilarProperties($property, $limit);
        
        // Track behavior
        if ($request->user()) {
            UserBehavior::track($request->user()->id, 'view_similar', $propertyId);
        }
        
        return response()->json([
            'success' => true,
            'property_id' => $propertyId,
            'similar_properties' => $similarProperties,
        ]);
    }
    
    /**
     * Track recommendation interaction
     */
    public function trackInteraction(Request $request, $recommendationId)
    {
        $request->validate([
            'action' => 'required|in:clicked,viewed,booked,dismissed',
        ]);
        
        $recommendation = PropertyRecommendation::findOrFail($recommendationId);
        
        // Ensure the recommendation belongs to the user
        if ($recommendation->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        switch ($request->action) {
            case 'clicked':
                $recommendation->markClicked();
                UserBehavior::track($request->user()->id, 'recommendation_clicked', $recommendation->property_id);
                break;
            case 'booked':
                $recommendation->markBooked();
                UserBehavior::track($request->user()->id, 'recommendation_booked', $recommendation->property_id);
                break;
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Interaction tracked successfully',
        ]);
    }
    
    /**
     * Get recommendation stats for admin
     */
    public function getRecommendationStats(Request $request)
    {
        $stats = [
            'total_recommendations' => PropertyRecommendation::count(),
            'active_recommendations' => PropertyRecommendation::where('valid_until', '>', now())->count(),
            'shown_count' => PropertyRecommendation::where('shown', true)->count(),
            'clicked_count' => PropertyRecommendation::where('clicked', true)->count(),
            'booked_count' => PropertyRecommendation::where('booked', true)->count(),
            'click_through_rate' => $this->calculateCTR(),
            'conversion_rate' => $this->calculateConversionRate(),
            'average_score' => PropertyRecommendation::avg('score'),
            'top_performing_factors' => $this->getTopPerformingFactors(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
    
    /**
     * Generate recommendations for a user using ML algorithms
     */
    private function generateRecommendationsForUser(int $userId)
    {
        // Check if valid recommendations exist
        $existingCount = PropertyRecommendation::where('user_id', $userId)
            ->where('valid_until', '>', now())
            ->count();
        
        if ($existingCount >= 10) {
            return; // Already have enough valid recommendations
        }
        
        $user = User::findOrFail($userId);
        
        // Collaborative Filtering: Find similar users
        $similarUsers = $this->findSimilarUsers($userId);
        
        // Content-based: Analyze user preferences
        $userPreferences = $this->analyzeUserPreferences($userId);
        
        // Get candidate properties
        $candidateProperties = Property::where('status', 'active')
            ->whereDoesntHave('bookings', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->limit(100)
            ->get();
        
        foreach ($candidateProperties as $property) {
            $score = $this->calculateRecommendationScore($userId, $property, $similarUsers, $userPreferences);
            
            if ($score > 60) { // Threshold
                $factors = $this->calculateFactors($userId, $property, $userPreferences);
                
                PropertyRecommendation::updateOrCreate(
                    [
                        'user_id' => $userId,
                        'property_id' => $property->id,
                    ],
                    [
                        'score' => $score,
                        'reason' => $this->generateReason($factors),
                        'factors' => $factors,
                        'valid_until' => now()->addDays(7),
                    ]
                );
            }
        }
    }
    
    /**
     * Find similar users based on behavior
     */
    private function findSimilarUsers(int $userId): array
    {
        // Find users with similar bookings, wishlists, and searches
        $userBookings = Booking::where('user_id', $userId)->pluck('property_id');
        $userWishlists = Wishlist::where('user_id', $userId)
            ->with('items')
            ->first();
        
        $wishlistPropertyIds = $userWishlists ? $userWishlists->items->pluck('property_id') : collect([]);
        
        $commonPropertyIds = $userBookings->merge($wishlistPropertyIds)->unique();
        
        if ($commonPropertyIds->isEmpty()) {
            return [];
        }
        
        $similarUsers = User::whereHas('bookings', function ($query) use ($commonPropertyIds) {
                $query->whereIn('property_id', $commonPropertyIds);
            })
            ->where('id', '!=', $userId)
            ->limit(10)
            ->pluck('id')
            ->toArray();
        
        return $similarUsers;
    }
    
    /**
     * Analyze user preferences from past behavior
     */
    private function analyzeUserPreferences(int $userId): array
    {
        $behaviors = UserBehavior::where('user_id', $userId)
            ->where('action_at', '>=', now()->subMonths(6))
            ->get();
        
        $bookings = Booking::where('user_id', $userId)->with('property')->get();
        $wishlists = Wishlist::where('user_id', $userId)->with('items.property')->first();
        
        $preferences = [
            'favorite_cities' => [],
            'price_range' => ['min' => 0, 'max' => 999999],
            'preferred_types' => [],
            'preferred_amenities' => [],
            'average_guests' => 2,
            'booking_frequency' => $bookings->count(),
        ];
        
        // Analyze bookings
        if ($bookings->isNotEmpty()) {
            $prices = $bookings->pluck('property.price_per_night')->filter();
            $preferences['price_range'] = [
                'min' => $prices->min() * 0.8,
                'max' => $prices->max() * 1.2,
            ];
            
            $preferences['average_guests'] = $bookings->avg('number_of_guests') ?? 2;
            $preferences['favorite_cities'] = $bookings->pluck('property.city')
                ->filter()
                ->countBy()
                ->sortDesc()
                ->take(3)
                ->keys()
                ->toArray();
        }
        
        // Analyze wishlist
        if ($wishlists && $wishlists->items->isNotEmpty()) {
            $wishlistProperties = $wishlists->items->pluck('property')->filter();
            
            if ($wishlistProperties->isNotEmpty()) {
                $preferences['preferred_types'] = $wishlistProperties
                    ->pluck('type')
                    ->countBy()
                    ->sortDesc()
                    ->take(3)
                    ->keys()
                    ->toArray();
            }
        }
        
        return $preferences;
    }
    
    /**
     * Calculate recommendation score
     */
    private function calculateRecommendationScore(int $userId, Property $property, array $similarUsers, array $preferences): float
    {
        $score = 0;
        
        // Collaborative filtering score (40%)
        $collaborativeScore = $this->calculateCollaborativeScore($property, $similarUsers);
        $score += $collaborativeScore * 0.4;
        
        // Content-based score (40%)
        $contentScore = $this->calculateContentScore($property, $preferences);
        $score += $contentScore * 0.4;
        
        // Popularity score (10%)
        $popularityScore = $this->calculatePopularityScore($property);
        $score += $popularityScore * 0.1;
        
        // Recency bonus (10%)
        $recencyScore = $property->created_at->diffInDays(now()) < 30 ? 100 : 50;
        $score += $recencyScore * 0.1;
        
        return round($score, 2);
    }
    
    /**
     * Calculate collaborative filtering score
     */
    private function calculateCollaborativeScore(Property $property, array $similarUsers): float
    {
        if (empty($similarUsers)) {
            return 50; // Neutral score
        }
        
        $interactionCount = Booking::whereIn('user_id', $similarUsers)
            ->where('property_id', $property->id)
            ->count();
        
        $wishlistCount = DB::table('wishlist_items')
            ->whereIn('wishlist_id', function ($query) use ($similarUsers) {
                $query->select('id')
                    ->from('wishlists')
                    ->whereIn('user_id', $similarUsers);
            })
            ->where('property_id', $property->id)
            ->count();
        
        $totalInteractions = $interactionCount + $wishlistCount;
        
        // Normalize to 0-100
        return min(100, ($totalInteractions / count($similarUsers)) * 100);
    }
    
    /**
     * Calculate content-based score
     */
    private function calculateContentScore(Property $property, array $preferences): float
    {
        $score = 0;
        $factors = 0;
        
        // Price match
        if ($property->price_per_night >= $preferences['price_range']['min'] && 
            $property->price_per_night <= $preferences['price_range']['max']) {
            $score += 30;
        } else {
            $score += 10;
        }
        $factors++;
        
        // Location match
        if (!empty($preferences['favorite_cities']) && in_array($property->city, $preferences['favorite_cities'])) {
            $score += 30;
        } else {
            $score += 10;
        }
        $factors++;
        
        // Type match
        if (!empty($preferences['preferred_types']) && in_array($property->type, $preferences['preferred_types'])) {
            $score += 20;
        } else {
            $score += 10;
        }
        $factors++;
        
        // Capacity match
        if ($property->guests >= $preferences['average_guests']) {
            $score += 20;
        } else {
            $score += 5;
        }
        $factors++;
        
        return ($score / $factors);
    }
    
    /**
     * Calculate popularity score
     */
    private function calculatePopularityScore(Property $property): float
    {
        $bookingCount = $property->bookings()->count();
        $averageRating = $property->reviews()->avg('rating') ?? 0;
        $reviewCount = $property->reviews()->count();
        
        $popularityScore = ($bookingCount * 2) + ($averageRating * 10) + $reviewCount;
        
        return min(100, $popularityScore);
    }
    
    /**
     * Calculate factors for recommendation
     */
    private function calculateFactors(int $userId, Property $property, array $preferences): array
    {
        $factors = [];
        
        if (!empty($preferences['favorite_cities']) && in_array($property->city, $preferences['favorite_cities'])) {
            $factors[] = 'favorite_location';
        }
        
        if ($property->price_per_night >= $preferences['price_range']['min'] && 
            $property->price_per_night <= $preferences['price_range']['max']) {
            $factors[] = 'price_match';
        }
        
        if ($property->reviews()->avg('rating') >= 4.5) {
            $factors[] = 'highly_rated';
        }
        
        if ($property->created_at->diffInDays(now()) < 30) {
            $factors[] = 'new_listing';
        }
        
        if ($property->bookings()->count() > 10) {
            $factors[] = 'popular';
        }
        
        return $factors;
    }
    
    /**
     * Generate human-readable reason
     */
    private function generateReason(array $factors): string
    {
        $reasons = [
            'favorite_location' => 'In your favorite location',
            'price_match' => 'Matches your budget',
            'highly_rated' => 'Highly rated by guests',
            'new_listing' => 'New listing',
            'popular' => 'Popular choice',
        ];
        
        $selectedReasons = array_intersect_key($reasons, array_flip($factors));
        
        if (empty($selectedReasons)) {
            return 'Recommended for you';
        }
        
        return implode(', ', array_slice($selectedReasons, 0, 2));
    }
    
    /**
     * Find similar properties using content-based filtering
     */
    private function findSimilarProperties(Property $property, int $limit): array
    {
        $candidates = Property::where('id', '!=', $property->id)
            ->where('status', 'active')
            ->get();
        
        $similarities = [];
        
        foreach ($candidates as $candidate) {
            $score = $this->calculateSimilarityScore($property, $candidate);
            $similarities[] = [
                'property' => $candidate,
                'similarity_score' => $score,
                'similarity_factors' => $this->getSimilarityFactors($property, $candidate),
            ];
        }
        
        // Sort by similarity score
        usort($similarities, fn($a, $b) => $b['similarity_score'] <=> $a['similarity_score']);
        
        return array_slice($similarities, 0, $limit);
    }
    
    /**
     * Calculate similarity score between two properties
     */
    private function calculateSimilarityScore(Property $property1, Property $property2): float
    {
        $score = 0;
        
        // Location similarity (30%)
        if ($property1->city === $property2->city) $score += 30;
        else if ($property1->state === $property2->state) $score += 15;
        
        // Type similarity (20%)
        if ($property1->type === $property2->type) $score += 20;
        
        // Price similarity (20%)
        $priceDiff = abs($property1->price_per_night - $property2->price_per_night);
        $priceScore = max(0, 20 - ($priceDiff / $property1->price_per_night * 20));
        $score += $priceScore;
        
        // Capacity similarity (15%)
        $capacityDiff = abs($property1->guests - $property2->guests);
        $capacityScore = max(0, 15 - ($capacityDiff * 3));
        $score += $capacityScore;
        
        // Bedrooms similarity (15%)
        $bedroomDiff = abs($property1->bedrooms - $property2->bedrooms);
        $bedroomScore = max(0, 15 - ($bedroomDiff * 5));
        $score += $bedroomScore;
        
        return round($score, 2);
    }
    
    /**
     * Get similarity factors
     */
    private function getSimilarityFactors(Property $property1, Property $property2): array
    {
        $factors = [];
        
        if ($property1->city === $property2->city) {
            $factors[] = 'same_city';
        }
        
        if ($property1->type === $property2->type) {
            $factors[] = 'same_type';
        }
        
        if (abs($property1->price_per_night - $property2->price_per_night) / $property1->price_per_night < 0.2) {
            $factors[] = 'similar_price';
        }
        
        if ($property1->bedrooms === $property2->bedrooms) {
            $factors[] = 'same_bedrooms';
        }
        
        return $factors;
    }
    
    /**
     * Get recommendation type
     */
    private function getRecommendationType(array $factors): string
    {
        if (in_array('favorite_location', $factors)) return 'Location Match';
        if (in_array('highly_rated', $factors)) return 'Top Rated';
        if (in_array('new_listing', $factors)) return 'New Listing';
        if (in_array('popular', $factors)) return 'Trending';
        
        return 'Recommended';
    }
    
    /**
     * Calculate Click-Through Rate
     */
    private function calculateCTR(): float
    {
        $shown = PropertyRecommendation::where('shown', true)->count();
        $clicked = PropertyRecommendation::where('clicked', true)->count();
        
        return $shown > 0 ? round(($clicked / $shown) * 100, 2) : 0;
    }
    
    /**
     * Calculate Conversion Rate
     */
    private function calculateConversionRate(): float
    {
        $shown = PropertyRecommendation::where('shown', true)->count();
        $booked = PropertyRecommendation::where('booked', true)->count();
        
        return $shown > 0 ? round(($booked / $shown) * 100, 2) : 0;
    }
    
    /**
     * Get top performing factors
     */
    private function getTopPerformingFactors(): array
    {
        $recommendations = PropertyRecommendation::where('booked', true)->get();
        
        $factorCounts = [];
        foreach ($recommendations as $rec) {
            foreach ($rec->factors as $factor) {
                $factorCounts[$factor] = ($factorCounts[$factor] ?? 0) + 1;
            }
        }
        
        arsort($factorCounts);
        
        return array_slice($factorCounts, 0, 5, true);
    }
}
