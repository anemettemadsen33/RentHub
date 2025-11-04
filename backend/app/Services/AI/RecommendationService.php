<?php

namespace App\Services\AI;

use App\Models\Booking;
use App\Models\Property;
use App\Models\PropertyRecommendation;
use App\Models\User;
use App\Models\UserBehavior;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    private const CACHE_TTL = 3600; // 1 hour

    private const RECOMMENDATION_COUNT = 20;

    /**
     * Generate personalized recommendations for a user
     */
    public function generateRecommendations(int $userId): Collection
    {
        $cacheKey = "recommendations:user:{$userId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($userId) {
            // Get user preferences from behavior
            $userProfile = $this->buildUserProfile($userId);

            // Calculate recommendations using multiple algorithms
            $collaborative = $this->collaborativeFiltering($userId, $userProfile);
            $contentBased = $this->contentBasedFiltering($userId, $userProfile);
            $popularProperties = $this->popularProperties($userId);

            // Hybrid approach: combine scores
            $recommendations = $this->combineRecommendations([
                'collaborative' => $collaborative,
                'content_based' => $contentBased,
                'popular' => $popularProperties,
            ]);

            // Store recommendations
            $this->storeRecommendations($userId, $recommendations);

            return $recommendations;
        });
    }

    /**
     * Build user profile from behavior history
     */
    private function buildUserProfile(int $userId): array
    {
        $behaviors = UserBehavior::where('user_id', $userId)
            ->where('action_at', '>', now()->subMonths(6))
            ->get();

        $bookings = Booking::where('user_id', $userId)->with('property')->get();

        return [
            'viewed_properties' => $behaviors->where('action', 'view')->pluck('property_id')->toArray(),
            'bookmarked_properties' => $behaviors->where('action', 'bookmark')->pluck('property_id')->toArray(),
            'booked_properties' => $bookings->pluck('property_id')->toArray(),
            'preferred_types' => $this->extractPreferredTypes($bookings),
            'price_range' => $this->calculatePriceRange($bookings),
            'preferred_amenities' => $this->extractPreferredAmenities($behaviors),
            'location_preferences' => $this->extractLocationPreferences($bookings),
        ];
    }

    /**
     * Collaborative filtering: find similar users and their preferences
     */
    private function collaborativeFiltering(int $userId, array $userProfile): Collection
    {
        // Find users with similar behavior
        $similarUsers = $this->findSimilarUsers($userId, $userProfile);

        if ($similarUsers->isEmpty()) {
            return collect();
        }

        // Get properties liked by similar users but not yet seen by current user
        $recommendations = Property::whereIn('id', function ($query) use ($similarUsers, $userProfile) {
            $query->select('property_id')
                ->from('bookings')
                ->whereIn('user_id', $similarUsers->pluck('id'))
                ->whereNotIn('property_id', array_merge(
                    $userProfile['viewed_properties'],
                    $userProfile['booked_properties']
                ))
                ->groupBy('property_id')
                ->orderByRaw('COUNT(*) DESC')
                ->limit(10);
        })
            ->with(['amenities', 'reviews'])
            ->get()
            ->map(function ($property) {
                return [
                    'property' => $property,
                    'score' => $this->calculateCollaborativeScore($property),
                    'reason' => 'collaborative',
                ];
            });

        return $recommendations;
    }

    /**
     * Content-based filtering: recommend similar properties
     */
    private function contentBasedFiltering(int $userId, array $userProfile): Collection
    {
        if (empty($userProfile['booked_properties']) && empty($userProfile['bookmarked_properties'])) {
            return collect();
        }

        $referenceProperties = array_merge(
            $userProfile['booked_properties'],
            $userProfile['bookmarked_properties']
        );

        $recommendations = Property::where('status', 'active')
            ->whereNotIn('id', array_merge(
                $userProfile['viewed_properties'],
                $userProfile['booked_properties']
            ))
            ->get()
            ->map(function ($property) use ($referenceProperties) {
                $similarity = $this->calculateSimilarity($property, $referenceProperties);

                return [
                    'property' => $property,
                    'score' => $similarity,
                    'reason' => 'content_based',
                ];
            })
            ->filter(fn ($item) => $item['score'] > 0.5)
            ->sortByDesc('score')
            ->take(10);

        return $recommendations;
    }

    /**
     * Get popular properties
     */
    private function popularProperties(int $userId): Collection
    {
        $userProfile = UserBehavior::where('user_id', $userId)
            ->where('action', 'view')
            ->exists() ? $this->buildUserProfile($userId) : [];

        $viewedIds = $userProfile['viewed_properties'] ?? [];
        $bookedIds = $userProfile['booked_properties'] ?? [];

        $popular = Property::select('properties.*')
            ->selectRaw('COUNT(DISTINCT bookings.id) as booking_count')
            ->selectRaw('AVG(reviews.rating) as avg_rating')
            ->leftJoin('bookings', 'properties.id', '=', 'bookings.property_id')
            ->leftJoin('reviews', 'properties.id', '=', 'reviews.property_id')
            ->where('properties.status', 'active')
            ->whereNotIn('properties.id', array_merge($viewedIds, $bookedIds))
            ->groupBy('properties.id')
            ->having('booking_count', '>', 5)
            ->orderByDesc('booking_count')
            ->orderByDesc('avg_rating')
            ->limit(10)
            ->get()
            ->map(function ($property) {
                return [
                    'property' => $property,
                    'score' => 0.7, // baseline score for popular items
                    'reason' => 'popular',
                ];
            });

        return $popular;
    }

    /**
     * Combine multiple recommendation sources
     */
    private function combineRecommendations(array $sources): Collection
    {
        $weights = [
            'collaborative' => 0.4,
            'content_based' => 0.4,
            'popular' => 0.2,
        ];

        $combined = collect();

        foreach ($sources as $type => $recommendations) {
            foreach ($recommendations as $rec) {
                $propertyId = $rec['property']->id;
                $weightedScore = $rec['score'] * $weights[$type];

                if ($combined->has($propertyId)) {
                    $existing = $combined->get($propertyId);
                    $existing['score'] += $weightedScore;
                    $combined->put($propertyId, $existing);
                } else {
                    $combined->put($propertyId, [
                        'property' => $rec['property'],
                        'score' => $weightedScore,
                        'reasons' => [$rec['reason']],
                    ]);
                }
            }
        }

        return $combined->sortByDesc('score')->take(self::RECOMMENDATION_COUNT)->values();
    }

    /**
     * Store recommendations in database
     */
    private function storeRecommendations(int $userId, Collection $recommendations): void
    {
        // Delete old recommendations
        PropertyRecommendation::where('user_id', $userId)
            ->where('valid_until', '<', now())
            ->delete();

        foreach ($recommendations as $rec) {
            PropertyRecommendation::updateOrCreate(
                [
                    'user_id' => $userId,
                    'property_id' => $rec['property']->id,
                ],
                [
                    'score' => $rec['score'] * 100, // Convert to 0-100 scale
                    'reason' => $rec['reasons'][0] ?? 'hybrid',
                    'factors' => [
                        'reasons' => $rec['reasons'] ?? [],
                        'calculated_at' => now()->toIso8601String(),
                    ],
                    'valid_until' => now()->addHours(24),
                ]
            );
        }
    }

    /**
     * Find users with similar behavior patterns
     */
    private function findSimilarUsers(int $userId, array $userProfile): Collection
    {
        if (empty($userProfile['booked_properties'])) {
            return collect();
        }

        return User::whereHas('bookings', function ($query) use ($userProfile) {
            $query->whereIn('property_id', $userProfile['booked_properties']);
        })
            ->where('id', '!=', $userId)
            ->limit(50)
            ->get();
    }

    /**
     * Calculate similarity between property and reference properties
     */
    private function calculateSimilarity(Property $property, array $referencePropertyIds): float
    {
        if (empty($referencePropertyIds)) {
            return 0;
        }

        $referenceProperties = Property::whereIn('id', $referencePropertyIds)->get();

        $similarities = $referenceProperties->map(function ($refProp) use ($property) {
            $score = 0;
            $maxScore = 5;

            // Type similarity
            if ($property->type === $refProp->type) {
                $score += 1;
            }

            // Price similarity (within 30%)
            $priceDiff = abs($property->price_per_night - $refProp->price_per_night) / $refProp->price_per_night;
            if ($priceDiff < 0.3) {
                $score += 1;
            }

            // Location similarity (same city)
            if ($property->city === $refProp->city) {
                $score += 1;
            }

            // Capacity similarity
            $capacityDiff = abs($property->guests - $refProp->guests);
            if ($capacityDiff <= 2) {
                $score += 1;
            }

            // Amenities overlap
            $propertyAmenities = $property->amenities->pluck('id')->toArray();
            $refAmenities = $refProp->amenities->pluck('id')->toArray();
            $overlap = count(array_intersect($propertyAmenities, $refAmenities));
            if ($overlap > 0) {
                $score += min($overlap / max(count($propertyAmenities), 1), 1);
            }

            return $score / $maxScore;
        });

        return $similarities->avg();
    }

    /**
     * Calculate collaborative filtering score
     */
    private function calculateCollaborativeScore(Property $property): float
    {
        $bookingCount = $property->bookings()->count();
        $avgRating = $property->reviews()->avg('rating') ?? 0;

        return min(($bookingCount / 10) * 0.5 + ($avgRating / 5) * 0.5, 1);
    }

    private function extractPreferredTypes(Collection $bookings): array
    {
        return $bookings->pluck('property.type')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->toArray();
    }

    private function calculatePriceRange(Collection $bookings): array
    {
        $prices = $bookings->pluck('property.price_per_night')->filter();

        if ($prices->isEmpty()) {
            return ['min' => 0, 'max' => 0];
        }

        return [
            'min' => $prices->min(),
            'max' => $prices->max(),
            'avg' => $prices->avg(),
        ];
    }

    private function extractPreferredAmenities(Collection $behaviors): array
    {
        $propertyIds = $behaviors->where('action', 'view')->pluck('property_id');

        return DB::table('amenity_property')
            ->whereIn('property_id', $propertyIds)
            ->select('amenity_id', DB::raw('COUNT(*) as count'))
            ->groupBy('amenity_id')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('amenity_id')
            ->toArray();
    }

    private function extractLocationPreferences(Collection $bookings): array
    {
        return $bookings->pluck('property.city')
            ->filter()
            ->countBy()
            ->sortDesc()
            ->keys()
            ->toArray();
    }
}
