<?php

namespace App\Services;

use App\Models\Property;
use App\Models\PricingRule;
use App\Models\PriceSuggestion;
use App\Models\Booking;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class PricingService
{
    /**
     * Calculate price for a specific date with all applicable rules
     */
    public function calculatePriceForDate(Property $property, Carbon $date): float
    {
        $basePrice = $property->price_per_night;
        
        // Get all applicable rules for this date, ordered by priority
        $rules = $property->pricingRules()
            ->active()
            ->byPriority()
            ->get()
            ->filter(function ($rule) use ($date) {
                return $rule->appliesTo($date);
            });

        // Apply rules in priority order
        foreach ($rules as $rule) {
            $basePrice = $rule->calculatePrice($basePrice);
        }

        return round($basePrice, 2);
    }

    /**
     * Calculate total price for a date range
     */
    public function calculateTotalPrice(Property $property, Carbon $checkIn, Carbon $checkOut): array
    {
        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
        $dailyPrices = [];
        $totalPrice = 0;

        foreach ($period as $date) {
            $price = $this->calculatePriceForDate($property, $date);
            $dailyPrices[$date->toDateString()] = $price;
            $totalPrice += $price;
        }

        // Add cleaning fee if exists
        $cleaningFee = $property->cleaning_fee ?? 0;
        $totalPrice += $cleaningFee;

        return [
            'base_price' => $property->price_per_night,
            'daily_prices' => $dailyPrices,
            'subtotal' => $totalPrice - $cleaningFee,
            'cleaning_fee' => $cleaningFee,
            'total_price' => $totalPrice,
            'nights' => $period->count(),
            'average_price_per_night' => $period->count() > 0 ? ($totalPrice - $cleaningFee) / $period->count() : 0,
        ];
    }

    /**
     * Generate AI-powered price suggestions for a property
     */
    public function generatePriceSuggestion(Property $property, Carbon $startDate, Carbon $endDate): PriceSuggestion
    {
        // Analyze market data
        $marketData = $this->analyzeMarket($property);
        
        // Calculate demand score
        $demandScore = $this->calculateDemandScore($property, $startDate, $endDate);
        
        // Get historical data
        $historicalData = $this->getHistoricalData($property, $startDate, $endDate);
        
        // Calculate suggested price using algorithm
        $suggestedPrice = $this->calculateOptimalPrice(
            $property,
            $marketData,
            $demandScore,
            $historicalData
        );

        // Calculate confidence score
        $confidenceScore = $this->calculateConfidenceScore($marketData, $historicalData);

        // Create price suggestion
        return PriceSuggestion::create([
            'property_id' => $property->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'current_price' => $property->price_per_night,
            'suggested_price' => $suggestedPrice['suggested'],
            'min_recommended_price' => $suggestedPrice['min'],
            'max_recommended_price' => $suggestedPrice['max'],
            'confidence_score' => $confidenceScore,
            'factors' => [
                'market_analysis' => $marketData,
                'demand_analysis' => $demandScore,
                'historical_data' => $historicalData,
                'algorithm_version' => '1.0',
            ],
            'market_average_price' => $marketData['average_price'] ?? 0,
            'competitor_count' => $marketData['competitor_count'] ?? 0,
            'occupancy_rate' => $marketData['area_occupancy'] ?? 0,
            'demand_score' => $demandScore['score'],
            'historical_price' => $historicalData['average_price'] ?? null,
            'historical_occupancy' => $historicalData['occupancy_rate'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(7), // Suggestion valid for 7 days
            'model_version' => 'v1.0-basic',
        ]);
    }

    /**
     * Analyze market data for similar properties in the area
     */
    protected function analyzeMarket(Property $property): array
    {
        // Find similar properties within 5km radius
        $similarProperties = Property::query()
            ->where('id', '!=', $property->id)
            ->where('type', $property->type)
            ->where('bedrooms', $property->bedrooms)
            ->active()
            ->get()
            ->filter(function ($p) use ($property) {
                // Simple distance calculation (you can use actual geo distance)
                return $this->calculateDistance(
                    $property->latitude,
                    $property->longitude,
                    $p->latitude,
                    $p->longitude
                ) <= 5; // 5km radius
            });

        $averagePrice = $similarProperties->avg('price_per_night') ?? $property->price_per_night;
        
        // Calculate area occupancy rate
        $areaOccupancy = $this->calculateAreaOccupancy($similarProperties);

        return [
            'competitor_count' => $similarProperties->count(),
            'average_price' => round($averagePrice, 2),
            'min_price' => $similarProperties->min('price_per_night') ?? 0,
            'max_price' => $similarProperties->max('price_per_night') ?? 0,
            'area_occupancy' => $areaOccupancy,
            'price_position' => $this->calculatePricePosition($property->price_per_night, $averagePrice),
        ];
    }

    /**
     * Calculate demand score based on various factors
     */
    protected function calculateDemandScore(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        $score = 50; // Base score
        $factors = [];

        // Check bookings in the period
        $existingBookings = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate]);
            })
            ->count();

        if ($existingBookings > 0) {
            $score += 20;
            $factors[] = 'High booking activity';
        }

        // Check season (summer/winter)
        $month = $startDate->month;
        if (in_array($month, [6, 7, 8])) { // Summer
            $score += 15;
            $factors[] = 'Summer peak season';
        } elseif (in_array($month, [12, 1])) { // Winter holidays
            $score += 10;
            $factors[] = 'Holiday season';
        }

        // Check weekends
        $weekendDays = 0;
        $period = CarbonPeriod::create($startDate, $endDate);
        foreach ($period as $date) {
            if ($date->isWeekend()) {
                $weekendDays++;
            }
        }
        
        if ($weekendDays > 0) {
            $score += min($weekendDays * 2, 10);
            $factors[] = "Contains {$weekendDays} weekend days";
        }

        // Check advance booking (early bird vs last minute)
        $daysUntil = now()->diffInDays($startDate);
        if ($daysUntil < 7) {
            $score += 15;
            $factors[] = 'Last-minute booking (high urgency)';
        } elseif ($daysUntil > 60) {
            $score -= 5;
            $factors[] = 'Far advance booking';
        }

        return [
            'score' => min(max($score, 0), 100), // Keep between 0-100
            'factors' => $factors,
        ];
    }

    /**
     * Get historical pricing and occupancy data
     */
    protected function getHistoricalData(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        // Get bookings from the same period last year
        $lastYearStart = $startDate->copy()->subYear();
        $lastYearEnd = $endDate->copy()->subYear();

        $historicalBookings = Booking::where('property_id', $property->id)
            ->where('status', 'completed')
            ->whereBetween('check_in', [$lastYearStart, $lastYearEnd])
            ->get();

        if ($historicalBookings->isEmpty()) {
            return [
                'has_data' => false,
            ];
        }

        return [
            'has_data' => true,
            'average_price' => $historicalBookings->avg('total_price'),
            'occupancy_rate' => $this->calculateHistoricalOccupancy($property, $lastYearStart, $lastYearEnd),
            'booking_count' => $historicalBookings->count(),
        ];
    }

    /**
     * Calculate optimal price using all available data
     */
    protected function calculateOptimalPrice(Property $property, array $marketData, array $demandScore, array $historicalData): array
    {
        $basePrice = $property->price_per_night;
        $marketAverage = $marketData['average_price'] ?? $basePrice;
        
        // Start with market average
        $suggested = $marketAverage;

        // Adjust based on demand score
        $demandAdjustment = ($demandScore['score'] - 50) / 100; // -0.5 to +0.5
        $suggested += $suggested * $demandAdjustment * 0.3; // Max 15% adjustment

        // Adjust based on historical performance (if available)
        if ($historicalData['has_data'] ?? false) {
            $historicalPrice = $historicalData['average_price'] ?? $basePrice;
            $suggested = ($suggested + $historicalPrice) / 2; // Average with historical
        }

        // Ensure minimum profitability
        $minPrice = $basePrice * 0.8; // Never go below 80% of current price
        $maxPrice = $basePrice * 1.5; // Cap at 150% of current price

        $suggested = max($minPrice, min($suggested, $maxPrice));

        return [
            'suggested' => round($suggested, 2),
            'min' => round($minPrice, 2),
            'max' => round($maxPrice, 2),
        ];
    }

    /**
     * Calculate confidence score for the suggestion
     */
    protected function calculateConfidenceScore(array $marketData, array $historicalData): int
    {
        $score = 30; // Base confidence

        // More competitors = higher confidence
        $competitorCount = $marketData['competitor_count'] ?? 0;
        if ($competitorCount >= 10) {
            $score += 30;
        } elseif ($competitorCount >= 5) {
            $score += 20;
        } elseif ($competitorCount >= 2) {
            $score += 10;
        }

        // Historical data increases confidence
        if ($historicalData['has_data'] ?? false) {
            $score += 25;
        }

        // Market data quality
        if (isset($marketData['area_occupancy']) && $marketData['area_occupancy'] > 0) {
            $score += 15;
        }

        return min($score, 100);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    protected function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate area occupancy rate
     */
    protected function calculateAreaOccupancy(Collection $properties): float
    {
        if ($properties->isEmpty()) {
            return 0;
        }

        $totalOccupancy = 0;
        $count = 0;

        foreach ($properties as $property) {
            $occupancy = $this->calculatePropertyOccupancy($property);
            if ($occupancy !== null) {
                $totalOccupancy += $occupancy;
                $count++;
            }
        }

        return $count > 0 ? round($totalOccupancy / $count, 2) : 0;
    }

    /**
     * Calculate property occupancy rate for last 30 days
     */
    protected function calculatePropertyOccupancy(Property $property): ?float
    {
        $startDate = now()->subDays(30);
        $endDate = now();
        
        return $this->calculateHistoricalOccupancy($property, $startDate, $endDate);
    }

    /**
     * Calculate historical occupancy rate
     */
    protected function calculateHistoricalOccupancy(Property $property, Carbon $startDate, Carbon $endDate): float
    {
        $totalDays = $startDate->diffInDays($endDate);
        
        $bookedDays = Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in', [$startDate, $endDate])
                    ->orWhereBetween('check_out', [$startDate, $endDate])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->where('check_in', '<=', $startDate)
                            ->where('check_out', '>=', $endDate);
                    });
            })
            ->get()
            ->sum(function ($booking) use ($startDate, $endDate) {
                $bookingStart = max($booking->check_in, $startDate);
                $bookingEnd = min($booking->check_out, $endDate);
                return $bookingStart->diffInDays($bookingEnd);
            });

        return $totalDays > 0 ? round(($bookedDays / $totalDays) * 100, 2) : 0;
    }

    /**
     * Calculate price position relative to market
     */
    protected function calculatePricePosition(float $currentPrice, float $marketAverage): string
    {
        if ($marketAverage == 0) {
            return 'unknown';
        }

        $difference = (($currentPrice - $marketAverage) / $marketAverage) * 100;

        if ($difference > 20) {
            return 'premium';
        } elseif ($difference > 10) {
            return 'above_average';
        } elseif ($difference < -20) {
            return 'budget';
        } elseif ($difference < -10) {
            return 'below_average';
        }

        return 'average';
    }

    /**
     * Get pricing calendar for a property
     */
    public function getPricingCalendar(Property $property, Carbon $startDate, Carbon $endDate): array
    {
        $period = CarbonPeriod::create($startDate, $endDate);
        $calendar = [];

        foreach ($period as $date) {
            $price = $this->calculatePriceForDate($property, $date);
            $calendar[$date->toDateString()] = [
                'date' => $date->toDateString(),
                'day_of_week' => $date->dayName,
                'price' => $price,
                'is_weekend' => $date->isWeekend(),
                'is_available' => $this->isDateAvailable($property, $date),
            ];
        }

        return $calendar;
    }

    /**
     * Check if a date is available for booking
     */
    protected function isDateAvailable(Property $property, Carbon $date): bool
    {
        return !Booking::where('property_id', $property->id)
            ->where('status', '!=', 'cancelled')
            ->where('check_in', '<=', $date)
            ->where('check_out', '>', $date)
            ->exists();
    }
}
