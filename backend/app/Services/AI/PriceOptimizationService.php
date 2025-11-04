<?php

namespace App\Services\AI;

use App\Models\Property;
use App\Models\PricePrediction;
use App\Models\RevenueSuggestion;
use App\Models\OccupancyPrediction;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PriceOptimizationService
{
    private const MODEL_VERSION = 'v1.0';

    /**
     * Generate price predictions for a property
     */
    public function predictPrices(int $propertyId, Carbon $startDate, Carbon $endDate): Collection
    {
        $property = Property::findOrFail($propertyId);
        $predictions = collect();

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $features = $this->extractFeatures($property, $currentDate);
            $predictedPrice = $this->calculateOptimalPrice($property, $features);
            $confidence = $this->calculateConfidence($property, $features);

            $prediction = PricePrediction::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'date' => $currentDate->toDateString(),
                ],
                [
                    'predicted_price' => $predictedPrice,
                    'confidence' => $confidence,
                    'features' => $features,
                    'model_version' => self::MODEL_VERSION,
                ]
            );

            $predictions->push($prediction);
            $currentDate->addDay();
        }

        return $predictions;
    }

    /**
     * Generate revenue optimization suggestions
     */
    public function generateRevenueSuggestions(int $propertyId): Collection
    {
        $property = Property::with(['bookings', 'reviews'])->findOrFail($propertyId);
        $suggestions = collect();

        // Analyze current performance
        $performance = $this->analyzePerformance($property);

        // Price adjustment suggestions
        if ($performance['occupancy_rate'] > 80) {
            $suggestions->push($this->suggestPriceIncrease($property, $performance));
        } elseif ($performance['occupancy_rate'] < 40) {
            $suggestions->push($this->suggestPriceDecrease($property, $performance));
        }

        // Minimum stay suggestions
        if ($performance['avg_booking_length'] < 3) {
            $suggestions->push($this->suggestMinimumStay($property, $performance));
        }

        // Discount suggestions for low seasons
        $lowSeasonPeriods = $this->identifyLowSeasons($property);
        foreach ($lowSeasonPeriods as $period) {
            $suggestions->push($this->suggestSeasonalDiscount($property, $period));
        }

        // Last-minute discount suggestions
        $upcomingAvailability = $this->getUpcomingAvailability($property);
        if ($upcomingAvailability->count() > 20) {
            $suggestions->push($this->suggestLastMinuteDiscount($property, $upcomingAvailability));
        }

        // Store suggestions
        foreach ($suggestions->filter() as $suggestion) {
            RevenueSuggestion::create($suggestion);
        }

        return $suggestions;
    }

    /**
     * Predict occupancy rates
     */
    public function predictOccupancy(int $propertyId, Carbon $startDate, Carbon $endDate): Collection
    {
        $property = Property::findOrFail($propertyId);
        $predictions = collect();

        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $factors = $this->getOccupancyFactors($property, $currentDate);
            $occupancyRate = $this->calculateOccupancyProbability($property, $factors);
            $confidence = $this->calculateOccupancyConfidence($property, $factors);

            $prediction = OccupancyPrediction::updateOrCreate(
                [
                    'property_id' => $propertyId,
                    'prediction_date' => $currentDate->toDateString(),
                ],
                [
                    'predicted_occupancy' => $occupancyRate,
                    'confidence' => $confidence,
                    'factors' => $factors,
                ]
            );

            $predictions->push($prediction);
            $currentDate->addDay();
        }

        return $predictions;
    }

    /**
     * Extract features for price prediction
     */
    private function extractFeatures(Property $property, Carbon $date): array
    {
        return [
            'day_of_week' => $date->dayOfWeek,
            'month' => $date->month,
            'is_weekend' => $date->isWeekend(),
            'is_holiday' => $this->isHoliday($date),
            'days_until_date' => now()->diffInDays($date),
            'season' => $this->getSeason($date),
            'local_events' => $this->getLocalEvents($property->city, $date),
            'historical_occupancy' => $this->getHistoricalOccupancy($property, $date),
            'competitor_avg_price' => $this->getCompetitorAveragePrice($property, $date),
            'demand_score' => $this->calculateDemandScore($property, $date),
        ];
    }

    /**
     * Calculate optimal price based on features
     */
    private function calculateOptimalPrice(Property $property, array $features): float
    {
        $basePrice = $property->price_per_night;

        // Weekend premium
        if ($features['is_weekend']) {
            $basePrice *= 1.15;
        }

        // Holiday premium
        if ($features['is_holiday']) {
            $basePrice *= 1.25;
        }

        // Seasonal adjustment
        $seasonalMultipliers = [
            'winter' => 0.85,
            'spring' => 1.0,
            'summer' => 1.2,
            'fall' => 1.05,
        ];
        $basePrice *= $seasonalMultipliers[$features['season']] ?? 1.0;

        // Demand adjustment
        $demandMultiplier = 1 + ($features['demand_score'] / 100);
        $basePrice *= $demandMultiplier;

        // Last-minute adjustment
        if ($features['days_until_date'] <= 7) {
            $basePrice *= 0.9; // 10% discount for last-minute
        }

        // Competitor pricing adjustment
        if ($features['competitor_avg_price'] > 0) {
            $competitivePrice = ($basePrice + $features['competitor_avg_price']) / 2;
            $basePrice = $basePrice * 0.7 + $competitivePrice * 0.3; // Weighted average
        }

        return round($basePrice, 2);
    }

    /**
     * Calculate confidence in prediction
     */
    private function calculateConfidence(Property $property, array $features): float
    {
        $confidence = 50.0; // Base confidence

        // More historical data = higher confidence
        $bookingCount = Booking::where('property_id', $property->id)->count();
        $confidence += min($bookingCount * 2, 30);

        // Recent bookings increase confidence
        $recentBookings = Booking::where('property_id', $property->id)
            ->where('created_at', '>', now()->subMonths(3))
            ->count();
        $confidence += min($recentBookings * 1.5, 15);

        // Reviews increase confidence
        $reviewCount = $property->reviews()->count();
        $confidence += min($reviewCount, 5);

        return min($confidence, 95.0);
    }

    /**
     * Analyze property performance
     */
    private function analyzePerformance(Property $property): array
    {
        $last90Days = now()->subDays(90);
        
        $bookings = Booking::where('property_id', $property->id)
            ->where('check_in', '>=', $last90Days)
            ->get();

        $bookedNights = $bookings->sum(function ($booking) {
            return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
        });

        $totalAvailableNights = 90;
        $occupancyRate = ($totalAvailableNights > 0) ? ($bookedNights / $totalAvailableNights) * 100 : 0;

        return [
            'occupancy_rate' => round($occupancyRate, 2),
            'total_bookings' => $bookings->count(),
            'total_revenue' => $bookings->sum('total_price'),
            'avg_booking_length' => $bookings->avg(function ($booking) {
                return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
            }),
            'avg_price' => $bookings->avg('total_price'),
            'cancellation_rate' => $this->calculateCancellationRate($property),
        ];
    }

    /**
     * Suggest price increase
     */
    private function suggestPriceIncrease(Property $property, array $performance): array
    {
        $currentPrice = $property->price_per_night;
        $suggestedIncrease = $currentPrice * 0.15; // 15% increase
        $newPrice = $currentPrice + $suggestedIncrease;

        return [
            'property_id' => $property->id,
            'suggestion_type' => 'price_increase',
            'description' => "High occupancy rate ({$performance['occupancy_rate']}%) suggests room for price increase",
            'parameters' => [
                'current_price' => $currentPrice,
                'suggested_price' => round($newPrice, 2),
                'increase_percentage' => 15,
            ],
            'expected_impact' => $suggestedIncrease * 30, // Estimated monthly impact
            'confidence' => 75.0,
            'valid_until' => now()->addDays(30),
        ];
    }

    /**
     * Suggest price decrease
     */
    private function suggestPriceDecrease(Property $property, array $performance): array
    {
        $currentPrice = $property->price_per_night;
        $suggestedDecrease = $currentPrice * 0.10; // 10% decrease
        $newPrice = $currentPrice - $suggestedDecrease;

        return [
            'property_id' => $property->id,
            'suggestion_type' => 'price_decrease',
            'description' => "Low occupancy rate ({$performance['occupancy_rate']}%) suggests price adjustment needed",
            'parameters' => [
                'current_price' => $currentPrice,
                'suggested_price' => round($newPrice, 2),
                'decrease_percentage' => 10,
            ],
            'expected_impact' => $suggestedDecrease * 15, // Lower but more bookings
            'confidence' => 70.0,
            'valid_until' => now()->addDays(30),
        ];
    }

    private function suggestMinimumStay(Property $property, array $performance): array
    {
        return [
            'property_id' => $property->id,
            'suggestion_type' => 'minimum_stay',
            'description' => "Short average booking length suggests implementing minimum stay requirement",
            'parameters' => [
                'current_min_stay' => $property->minimum_stay ?? 1,
                'suggested_min_stay' => 3,
            ],
            'expected_impact' => $property->price_per_night * 50, // Estimated impact
            'confidence' => 60.0,
            'valid_until' => now()->addDays(60),
        ];
    }

    private function suggestSeasonalDiscount(Property $property, array $period): ?array
    {
        return [
            'property_id' => $property->id,
            'suggestion_type' => 'discount',
            'description' => "Low season discount for period {$period['start']} to {$period['end']}",
            'parameters' => [
                'discount_percentage' => 15,
                'start_date' => $period['start'],
                'end_date' => $period['end'],
            ],
            'expected_impact' => $property->price_per_night * 20,
            'confidence' => 65.0,
            'valid_until' => Carbon::parse($period['start'])->subDays(7),
        ];
    }

    private function suggestLastMinuteDiscount(Property $property, Collection $availability): array
    {
        return [
            'property_id' => $property->id,
            'suggestion_type' => 'last_minute_discount',
            'description' => "Many available dates in next 30 days - offer last-minute discount",
            'parameters' => [
                'discount_percentage' => 10,
                'booking_window_days' => 7,
            ],
            'expected_impact' => $property->price_per_night * 10,
            'confidence' => 70.0,
            'valid_until' => now()->addDays(30),
        ];
    }

    private function getOccupancyFactors(Property $property, Carbon $date): array
    {
        return [
            'historical_bookings' => $this->getHistoricalOccupancy($property, $date),
            'price_point' => $property->price_per_night,
            'review_rating' => $property->reviews()->avg('rating') ?? 0,
            'is_weekend' => $date->isWeekend(),
            'season' => $this->getSeason($date),
            'days_until' => now()->diffInDays($date),
        ];
    }

    private function calculateOccupancyProbability(Property $property, array $factors): float
    {
        $probability = 50.0;

        // Historical data
        if ($factors['historical_bookings'] > 0.7) $probability += 20;
        elseif ($factors['historical_bookings'] > 0.5) $probability += 10;

        // Rating impact
        if ($factors['review_rating'] >= 4.5) $probability += 15;
        elseif ($factors['review_rating'] >= 4.0) $probability += 10;

        // Weekend boost
        if ($factors['is_weekend']) $probability += 10;

        // Booking window
        if ($factors['days_until'] > 60) $probability -= 20;
        elseif ($factors['days_until'] < 14) $probability += 10;

        return max(min($probability, 100), 0);
    }

    private function calculateOccupancyConfidence(Property $property, array $factors): float
    {
        $confidence = 60.0;
        
        $bookingCount = Booking::where('property_id', $property->id)->count();
        $confidence += min($bookingCount, 30);
        
        return min($confidence, 95.0);
    }

    private function isHoliday(Carbon $date): bool
    {
        // Simplified holiday detection - can be enhanced with actual holiday API
        $holidays = ['12-25', '01-01', '12-31', '07-04'];
        return in_array($date->format('m-d'), $holidays);
    }

    private function getSeason(Carbon $date): string
    {
        $month = $date->month;
        if (in_array($month, [12, 1, 2])) return 'winter';
        if (in_array($month, [3, 4, 5])) return 'spring';
        if (in_array($month, [6, 7, 8])) return 'summer';
        return 'fall';
    }

    private function getLocalEvents(string $city, Carbon $date): int
    {
        // Placeholder - integrate with events API
        return 0;
    }

    private function getHistoricalOccupancy(Property $property, Carbon $date): float
    {
        $sameMonth = Booking::where('property_id', $property->id)
            ->whereMonth('check_in', $date->month)
            ->whereYear('check_in', '<', $date->year)
            ->count();

        return min($sameMonth / 10, 1.0);
    }

    private function getCompetitorAveragePrice(Property $property, Carbon $date): float
    {
        return Property::where('city', $property->city)
            ->where('type', $property->type)
            ->where('id', '!=', $property->id)
            ->avg('price_per_night') ?? 0;
    }

    private function calculateDemandScore(Property $property, Carbon $date): float
    {
        // Simplified demand calculation
        $recentSearches = DB::table('user_behaviors')
            ->where('action', 'search')
            ->where('action_at', '>', now()->subDays(7))
            ->whereJsonContains('metadata->city', $property->city)
            ->count();

        return min($recentSearches / 10, 100);
    }

    private function calculateCancellationRate(Property $property): float
    {
        $total = Booking::where('property_id', $property->id)->count();
        if ($total === 0) return 0;

        $cancelled = Booking::where('property_id', $property->id)
            ->where('status', 'cancelled')
            ->count();

        return ($cancelled / $total) * 100;
    }

    private function identifyLowSeasons(Property $property): array
    {
        // Analyze historical data to identify low booking periods
        $bookingsByMonth = Booking::where('property_id', $property->id)
            ->selectRaw('MONTH(check_in) as month, COUNT(*) as count')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $avgBookings = count($bookingsByMonth) > 0 ? array_sum($bookingsByMonth) / count($bookingsByMonth) : 0;
        
        $lowSeasons = [];
        foreach ($bookingsByMonth as $month => $count) {
            if ($count < $avgBookings * 0.6) {
                $lowSeasons[] = [
                    'start' => Carbon::create(null, $month, 1)->format('Y-m-d'),
                    'end' => Carbon::create(null, $month, 1)->endOfMonth()->format('Y-m-d'),
                ];
            }
        }

        return $lowSeasons;
    }

    private function getUpcomingAvailability(Property $property): Collection
    {
        $bookedDates = Booking::where('property_id', $property->id)
            ->where('check_in', '>=', now())
            ->where('check_in', '<=', now()->addDays(30))
            ->get()
            ->flatMap(function ($booking) {
                return collect(Carbon::parse($booking->check_in)->range($booking->check_out));
            })
            ->map(fn($date) => $date->format('Y-m-d'))
            ->unique();

        $allDates = collect(now()->range(now()->addDays(30)))
            ->map(fn($date) => $date->format('Y-m-d'));

        return $allDates->diff($bookedDates);
    }
}
