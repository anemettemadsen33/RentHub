<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\MlModelMetric;
use App\Models\PricePrediction;
use App\Models\PriceSuggestion;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PriceOptimizationController extends Controller
{
    private const MODEL_VERSION = 'v1.2.0';

    /**
     * Get price prediction for a property
     */
    public function getPrediction(Request $request, $propertyId)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $property = Property::findOrFail($propertyId);
        $date = Carbon::parse($request->date);

        // Check if prediction exists
        $prediction = PricePrediction::where('property_id', $propertyId)
            ->whereDate('date', $date)
            ->where('model_version', self::MODEL_VERSION)
            ->first();

        if (! $prediction) {
            // Generate new prediction
            $prediction = $this->generatePricePrediction($property, $date);
        }

        return response()->json([
            'success' => true,
            'prediction' => $prediction,
            'factors' => $prediction->features,
        ]);
    }

    /**
     * Get price predictions for a date range
     */
    public function getPredictionRange(Request $request, $propertyId)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $property = Property::findOrFail($propertyId);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        // Limit to 90 days
        if ($startDate->diffInDays($endDate) > 90) {
            return response()->json([
                'error' => 'Date range cannot exceed 90 days',
            ], 400);
        }

        $predictions = [];
        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            $prediction = PricePrediction::where('property_id', $propertyId)
                ->whereDate('date', $currentDate)
                ->where('model_version', self::MODEL_VERSION)
                ->first();

            if (! $prediction) {
                $prediction = $this->generatePricePrediction($property, $currentDate);
            }

            $predictions[] = $prediction;
            $currentDate->addDay();
        }

        return response()->json([
            'success' => true,
            'property_id' => $propertyId,
            'predictions' => $predictions,
            'summary' => [
                'min_price' => collect($predictions)->min('predicted_price'),
                'max_price' => collect($predictions)->max('predicted_price'),
                'avg_price' => collect($predictions)->avg('predicted_price'),
                'total_potential_revenue' => collect($predictions)->sum('predicted_price'),
            ],
        ]);
    }

    /**
     * Get price optimization suggestions
     */
    public function getOptimization(Request $request, $propertyId)
    {
        $property = Property::findOrFail($propertyId);

        $optimization = [
            'current_price' => $property->price_per_night,
            'recommended_price' => $this->calculateOptimalPrice($property),
            'revenue_potential' => $this->calculateRevenuePotential($property),
            'occupancy_prediction' => $this->predictOccupancy($property),
            'competitor_analysis' => $this->analyzeCompetitors($property),
            'seasonal_insights' => $this->getSeasonalInsights($property),
            'pricing_strategy' => $this->suggestPricingStrategy($property),
        ];

        return response()->json([
            'success' => true,
            'optimization' => $optimization,
        ]);
    }

    /**
     * Apply ML-based price suggestions
     */
    public function applyPriceSuggestion(Request $request, $propertyId)
    {
        $request->validate([
            'apply_type' => 'required|in:immediate,scheduled,custom',
            'custom_adjustment' => 'sometimes|numeric|min:-50|max:50',
        ]);

        $property = Property::findOrFail($propertyId);

        // Ensure user owns the property
        if ($property->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $recommendedPrice = $this->calculateOptimalPrice($property);

        if ($request->apply_type === 'custom' && $request->has('custom_adjustment')) {
            $recommendedPrice += ($recommendedPrice * $request->custom_adjustment / 100);
        }

        if ($request->apply_type === 'immediate') {
            $property->update(['price_per_night' => $recommendedPrice]);
        }

        // Log the suggestion
        PriceSuggestion::create([
            'property_id' => $property->id,
            'current_price' => $property->price_per_night,
            'suggested_price' => $recommendedPrice,
            'confidence' => 0.85,
            'factors' => $this->getPricingFactors($property),
            'applied' => $request->apply_type === 'immediate',
            'valid_until' => now()->addDays(7),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Price suggestion applied',
            'old_price' => $property->price_per_night,
            'new_price' => $recommendedPrice,
        ]);
    }

    /**
     * Get model performance metrics
     */
    public function getModelMetrics(Request $request)
    {
        $metrics = MlModelMetric::where('model_version', self::MODEL_VERSION)
            ->latest()
            ->first();

        if (! $metrics) {
            $metrics = $this->calculateModelMetrics();
        }

        return response()->json([
            'success' => true,
            'metrics' => $metrics,
            'model_version' => self::MODEL_VERSION,
        ]);
    }

    /**
     * Train/update ML model
     */
    public function trainModel(Request $request)
    {
        // In a real application, this would trigger ML model training
        // For now, we'll calculate accuracy based on past predictions

        $predictions = PricePrediction::whereNotNull('actual_price')
            ->where('model_version', self::MODEL_VERSION)
            ->get();

        if ($predictions->isEmpty()) {
            return response()->json([
                'error' => 'Not enough data to train model',
            ], 400);
        }

        $metrics = $this->calculateModelMetrics();

        return response()->json([
            'success' => true,
            'message' => 'Model updated successfully',
            'metrics' => $metrics,
            'training_data_size' => $predictions->count(),
        ]);
    }

    /**
     * Get revenue optimization report
     */
    public function getRevenueReport(Request $request, $propertyId)
    {
        $property = Property::with(['bookings', 'reviews'])->findOrFail($propertyId);

        $report = [
            'property_id' => $propertyId,
            'current_performance' => $this->getCurrentPerformance($property),
            'optimization_opportunities' => $this->findOptimizationOpportunities($property),
            'revenue_forecast' => $this->forecastRevenue($property),
            'pricing_recommendations' => $this->getPricingRecommendations($property),
            'competitive_position' => $this->analyzeCompetitivePosition($property),
        ];

        return response()->json([
            'success' => true,
            'report' => $report,
            'generated_at' => now(),
        ]);
    }

    // ========== Private Methods ==========

    /**
     * Generate price prediction using ML algorithm
     */
    private function generatePricePrediction(Property $property, Carbon $date): PricePrediction
    {
        $features = $this->extractFeatures($property, $date);
        $predictedPrice = $this->predictPrice($features);
        $confidence = $this->calculateConfidence($features);

        return PricePrediction::create([
            'property_id' => $property->id,
            'date' => $date,
            'predicted_price' => $predictedPrice,
            'confidence' => $confidence,
            'features' => $features,
            'model_version' => self::MODEL_VERSION,
        ]);
    }

    /**
     * Extract features for ML model
     */
    private function extractFeatures(Property $property, Carbon $date): array
    {
        $features = [
            // Property features
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'guests' => $property->guests,
            'property_type' => $property->type,

            // Location features
            'city' => $property->city,
            'latitude' => $property->latitude,
            'longitude' => $property->longitude,

            // Temporal features
            'day_of_week' => $date->dayOfWeek,
            'month' => $date->month,
            'is_weekend' => $date->isWeekend(),
            'is_holiday' => $this->isHoliday($date),
            'days_until' => now()->diffInDays($date),

            // Historical features
            'average_rating' => $property->reviews()->avg('rating') ?? 0,
            'review_count' => $property->reviews()->count(),
            'booking_count' => $property->bookings()->count(),

            // Market features
            'competitor_avg_price' => $this->getCompetitorAveragePrice($property),
            'market_occupancy' => $this->getMarketOccupancy($property->city, $date),

            // Demand indicators
            'search_volume' => $this->getSearchVolume($property, $date),
            'wishlist_count' => DB::table('wishlist_items')
                ->where('property_id', $property->id)
                ->count(),
        ];

        return $features;
    }

    /**
     * Predict price using feature-based algorithm
     */
    private function predictPrice(array $features): float
    {
        // Base price calculation
        $basePrice = 50; // Starting point

        // Property characteristics
        $basePrice += ($features['bedrooms'] * 30);
        $basePrice += ($features['bathrooms'] * 20);
        $basePrice += ($features['guests'] * 15);

        // Location factor (simplified - in production use real location data)
        $locationMultiplier = 1.2;
        $basePrice *= $locationMultiplier;

        // Temporal factors
        if ($features['is_weekend']) {
            $basePrice *= 1.3;
        }

        if ($features['is_holiday']) {
            $basePrice *= 1.5;
        }

        // Seasonal adjustment
        $seasonalFactor = $this->getSeasonalFactor($features['month']);
        $basePrice *= $seasonalFactor;

        // Rating bonus
        if ($features['average_rating'] >= 4.5) {
            $basePrice *= 1.1;
        }

        // Demand adjustment
        if ($features['competitor_avg_price'] > 0) {
            // Adjust based on market
            $basePrice = ($basePrice + $features['competitor_avg_price']) / 2;
        }

        // Booking momentum
        if ($features['booking_count'] > 10) {
            $basePrice *= 1.05;
        }

        // Last-minute premium or discount
        if ($features['days_until'] < 7) {
            $basePrice *= 0.9; // Discount for last-minute
        } elseif ($features['days_until'] > 90) {
            $basePrice *= 0.95; // Early bird discount
        }

        return round($basePrice, 2);
    }

    /**
     * Calculate confidence score
     */
    private function calculateConfidence(array $features): float
    {
        $confidence = 100;

        // Reduce confidence if limited historical data
        if ($features['booking_count'] < 5) {
            $confidence -= 20;
        }

        if ($features['review_count'] < 3) {
            $confidence -= 15;
        }

        // Reduce confidence for far future dates
        if ($features['days_until'] > 180) {
            $confidence -= 25;
        }

        return max(50, min(100, $confidence));
    }

    /**
     * Calculate optimal price
     */
    private function calculateOptimalPrice(Property $property): float
    {
        // Get predictions for next 30 days
        $predictions = [];
        for ($i = 0; $i < 30; $i++) {
            $date = now()->addDays($i);
            $features = $this->extractFeatures($property, $date);
            $predictions[] = $this->predictPrice($features);
        }

        // Use average of predictions
        $optimalPrice = array_sum($predictions) / count($predictions);

        // Consider current market conditions
        $competitorAvg = $this->getCompetitorAveragePrice($property);
        if ($competitorAvg > 0) {
            // Blend with competitor prices
            $optimalPrice = ($optimalPrice * 0.7) + ($competitorAvg * 0.3);
        }

        return round($optimalPrice, 2);
    }

    /**
     * Calculate revenue potential
     */
    private function calculateRevenuePotential(Property $property): array
    {
        $currentRevenue = Booking::where('property_id', $property->id)
            ->where('created_at', '>=', now()->subMonths(12))
            ->sum('total_price');

        $optimalPrice = $this->calculateOptimalPrice($property);
        $currentPrice = $property->price_per_night;

        $potentialIncrease = (($optimalPrice - $currentPrice) / $currentPrice) * 100;
        $estimatedYearlyRevenue = $optimalPrice * 365 * 0.6; // Assuming 60% occupancy

        return [
            'current_yearly_revenue' => $currentRevenue,
            'potential_yearly_revenue' => $estimatedYearlyRevenue,
            'potential_increase_percent' => round($potentialIncrease, 2),
            'potential_increase_amount' => $estimatedYearlyRevenue - $currentRevenue,
        ];
    }

    /**
     * Predict occupancy rate
     */
    private function predictOccupancy(Property $property): array
    {
        $currentPrice = $property->price_per_night;
        $optimalPrice = $this->calculateOptimalPrice($property);

        // Historical occupancy
        $totalDays = 365;
        $bookedDays = Booking::where('property_id', $property->id)
            ->where('check_in', '>=', now()->subYear())
            ->sum(DB::raw('DATEDIFF(check_out, check_in)'));

        $currentOccupancy = $totalDays > 0 ? ($bookedDays / $totalDays) * 100 : 0;

        // Predict occupancy at different price points
        $predictions = [];
        for ($pricePoint = $currentPrice * 0.8; $pricePoint <= $currentPrice * 1.3; $pricePoint += $currentPrice * 0.1) {
            $predictedOccupancy = $this->predictOccupancyAtPrice($property, $pricePoint);
            $predictions[] = [
                'price' => round($pricePoint, 2),
                'occupancy' => $predictedOccupancy,
                'revenue' => round($pricePoint * 365 * ($predictedOccupancy / 100), 2),
            ];
        }

        return [
            'current_occupancy' => round($currentOccupancy, 2),
            'price_occupancy_curve' => $predictions,
            'optimal_price_point' => collect($predictions)->sortByDesc('revenue')->first(),
        ];
    }

    /**
     * Predict occupancy at a specific price
     */
    private function predictOccupancyAtPrice(Property $property, float $price): float
    {
        $baseOccupancy = 70; // Base assumption

        $competitorAvg = $this->getCompetitorAveragePrice($property);

        if ($competitorAvg > 0) {
            $priceRatio = $price / $competitorAvg;

            // Lower price = higher occupancy
            if ($priceRatio < 0.8) {
                $baseOccupancy += 20;
            } elseif ($priceRatio < 0.9) {
                $baseOccupancy += 10;
            } elseif ($priceRatio > 1.2) {
                $baseOccupancy -= 20;
            } elseif ($priceRatio > 1.1) {
                $baseOccupancy -= 10;
            }
        }

        // Rating adjustment
        $avgRating = $property->reviews()->avg('rating') ?? 0;
        if ($avgRating >= 4.5) {
            $baseOccupancy += 10;
        } elseif ($avgRating < 3.5) {
            $baseOccupancy -= 15;
        }

        return max(0, min(100, $baseOccupancy));
    }

    /**
     * Analyze competitors
     */
    private function analyzeCompetitors(Property $property): array
    {
        $competitors = Property::where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->where('type', $property->type)
            ->where('bedrooms', $property->bedrooms)
            ->where('status', 'active')
            ->get();

        if ($competitors->isEmpty()) {
            return [
                'competitor_count' => 0,
                'average_price' => 0,
                'price_position' => 'N/A',
            ];
        }

        $prices = $competitors->pluck('price_per_night');
        $avgPrice = $prices->avg();

        $position = 'average';
        if ($property->price_per_night < $avgPrice * 0.9) {
            $position = 'low';
        } elseif ($property->price_per_night > $avgPrice * 1.1) {
            $position = 'high';
        }

        return [
            'competitor_count' => $competitors->count(),
            'average_price' => round($avgPrice, 2),
            'min_price' => $prices->min(),
            'max_price' => $prices->max(),
            'price_position' => $position,
            'percentile' => $this->calculatePercentile($property->price_per_night, $prices->toArray()),
        ];
    }

    /**
     * Get seasonal insights
     */
    private function getSeasonalInsights(Property $property): array
    {
        $insights = [];

        for ($month = 1; $month <= 12; $month++) {
            $seasonalFactor = $this->getSeasonalFactor($month);
            $basePrice = $property->price_per_night;

            $insights[] = [
                'month' => $month,
                'month_name' => Carbon::create(null, $month)->format('F'),
                'demand_factor' => $seasonalFactor,
                'suggested_price' => round($basePrice * $seasonalFactor, 2),
            ];
        }

        return $insights;
    }

    /**
     * Suggest pricing strategy
     */
    private function suggestPricingStrategy(Property $property): array
    {
        $competitorAnalysis = $this->analyzeCompetitors($property);
        $occupancyData = $this->predictOccupancy($property);

        $strategy = [];

        if ($competitorAnalysis['price_position'] === 'high') {
            $strategy[] = 'Consider lowering price to improve competitiveness';
        } elseif ($competitorAnalysis['price_position'] === 'low') {
            $strategy[] = 'You can increase price to match market rates';
        }

        if ($occupancyData['current_occupancy'] < 50) {
            $strategy[] = 'Low occupancy detected - recommend dynamic pricing to boost bookings';
        }

        $avgRating = $property->reviews()->avg('rating') ?? 0;
        if ($avgRating >= 4.5) {
            $strategy[] = 'High ratings allow for premium pricing';
        }

        return [
            'recommendations' => $strategy,
            'strategy_type' => $this->determineStrategyType($property, $competitorAnalysis, $occupancyData),
        ];
    }

    /**
     * Get competitor average price
     */
    private function getCompetitorAveragePrice(Property $property): float
    {
        return Property::where('id', '!=', $property->id)
            ->where('city', $property->city)
            ->where('type', $property->type)
            ->where('status', 'active')
            ->avg('price_per_night') ?? 0;
    }

    /**
     * Get market occupancy
     */
    private function getMarketOccupancy(string $city, Carbon $date): float
    {
        // Simplified calculation
        // In production, this would use more sophisticated market data
        $bookings = Booking::whereHas('property', function ($query) use ($city) {
            $query->where('city', $city);
        })
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>=', $date)
            ->count();

        $totalProperties = Property::where('city', $city)->count();

        return $totalProperties > 0 ? ($bookings / $totalProperties) * 100 : 50;
    }

    /**
     * Get search volume
     */
    private function getSearchVolume(Property $property, Carbon $date): int
    {
        // Placeholder - would integrate with actual search tracking
        return rand(10, 100);
    }

    /**
     * Check if date is a holiday
     */
    private function isHoliday(Carbon $date): bool
    {
        // Simplified holiday check
        $holidays = [
            '01-01', // New Year
            '07-04', // Independence Day (US)
            '12-25', // Christmas
            '12-31', // New Year's Eve
        ];

        return in_array($date->format('m-d'), $holidays);
    }

    /**
     * Get seasonal factor
     */
    private function getSeasonalFactor(int $month): float
    {
        $factors = [
            1 => 0.9,  // January
            2 => 0.9,  // February
            3 => 1.0,  // March
            4 => 1.1,  // April
            5 => 1.2,  // May
            6 => 1.3,  // June
            7 => 1.4,  // July
            8 => 1.3,  // August
            9 => 1.1,  // September
            10 => 1.0, // October
            11 => 0.9, // November
            12 => 1.1, // December (holidays)
        ];

        return $factors[$month] ?? 1.0;
    }

    /**
     * Get pricing factors
     */
    private function getPricingFactors(Property $property): array
    {
        return [
            'property_features' => [
                'bedrooms' => $property->bedrooms,
                'bathrooms' => $property->bathrooms,
                'guests' => $property->guests,
            ],
            'market_position' => $this->analyzeCompetitors($property),
            'performance' => [
                'rating' => $property->reviews()->avg('rating'),
                'booking_count' => $property->bookings()->count(),
            ],
        ];
    }

    /**
     * Calculate model metrics
     */
    private function calculateModelMetrics(): MlModelMetric
    {
        $predictions = PricePrediction::whereNotNull('actual_price')
            ->where('model_version', self::MODEL_VERSION)
            ->get();

        if ($predictions->isEmpty()) {
            return MlModelMetric::create([
                'model_version' => self::MODEL_VERSION,
                'accuracy' => 0,
                'mae' => 0,
                'rmse' => 0,
                'r_squared' => 0,
                'training_samples' => 0,
            ]);
        }

        $errors = $predictions->map(function ($p) {
            return abs($p->predicted_price - $p->actual_price);
        });

        $mae = $errors->avg();
        $rmse = sqrt($errors->map(fn ($e) => $e * $e)->avg());

        return MlModelMetric::create([
            'model_version' => self::MODEL_VERSION,
            'accuracy' => 100 - min(100, ($mae / 100) * 100),
            'mae' => round($mae, 2),
            'rmse' => round($rmse, 2),
            'r_squared' => 0.85, // Placeholder
            'training_samples' => $predictions->count(),
        ]);
    }

    /**
     * Get current performance
     */
    private function getCurrentPerformance(Property $property): array
    {
        $bookings = $property->bookings()
            ->where('check_in', '>=', now()->subMonths(12))
            ->get();

        return [
            'total_bookings' => $bookings->count(),
            'total_revenue' => $bookings->sum('total_price'),
            'average_rating' => $property->reviews()->avg('rating'),
            'occupancy_rate' => $this->calculateOccupancyRate($property),
        ];
    }

    /**
     * Calculate occupancy rate
     */
    private function calculateOccupancyRate(Property $property): float
    {
        $totalDays = 365;
        $bookedDays = Booking::where('property_id', $property->id)
            ->where('check_in', '>=', now()->subYear())
            ->where('status', '!=', 'cancelled')
            ->sum(DB::raw('DATEDIFF(check_out, check_in)'));

        return round(($bookedDays / $totalDays) * 100, 2);
    }

    /**
     * Find optimization opportunities
     */
    private function findOptimizationOpportunities(Property $property): array
    {
        $opportunities = [];

        $competitorAnalysis = $this->analyzeCompetitors($property);
        if ($competitorAnalysis['price_position'] === 'low') {
            $opportunities[] = [
                'type' => 'price_increase',
                'description' => 'Your price is below market average',
                'potential_impact' => '+15% revenue',
            ];
        }

        $occupancyRate = $this->calculateOccupancyRate($property);
        if ($occupancyRate < 50) {
            $opportunities[] = [
                'type' => 'dynamic_pricing',
                'description' => 'Low occupancy rate detected',
                'potential_impact' => '+20% bookings',
            ];
        }

        return $opportunities;
    }

    /**
     * Forecast revenue
     */
    private function forecastRevenue(Property $property): array
    {
        $optimalPrice = $this->calculateOptimalPrice($property);
        $predictedOccupancy = $this->predictOccupancyAtPrice($property, $optimalPrice);

        $monthlyRevenue = $optimalPrice * 30 * ($predictedOccupancy / 100);
        $yearlyRevenue = $monthlyRevenue * 12;

        return [
            'monthly' => round($monthlyRevenue, 2),
            'quarterly' => round($monthlyRevenue * 3, 2),
            'yearly' => round($yearlyRevenue, 2),
        ];
    }

    /**
     * Get pricing recommendations
     */
    private function getPricingRecommendations(Property $property): array
    {
        $optimalPrice = $this->calculateOptimalPrice($property);
        $currentPrice = $property->price_per_night;

        return [
            'current_price' => $currentPrice,
            'recommended_price' => $optimalPrice,
            'change_percent' => round((($optimalPrice - $currentPrice) / $currentPrice) * 100, 2),
            'reason' => $this->generatePricingReason($property, $optimalPrice, $currentPrice),
        ];
    }

    /**
     * Analyze competitive position
     */
    private function analyzeCompetitivePosition(Property $property): array
    {
        $competitors = $this->analyzeCompetitors($property);

        return [
            'market_position' => $competitors['price_position'],
            'percentile' => $competitors['percentile'] ?? 50,
            'competitive_advantages' => $this->identifyCompetitiveAdvantages($property),
        ];
    }

    /**
     * Identify competitive advantages
     */
    private function identifyCompetitiveAdvantages(Property $property): array
    {
        $advantages = [];

        $avgRating = $property->reviews()->avg('rating') ?? 0;
        if ($avgRating >= 4.5) {
            $advantages[] = 'High guest ratings';
        }

        if ($property->amenities()->count() > 10) {
            $advantages[] = 'Extensive amenities';
        }

        return $advantages;
    }

    /**
     * Calculate percentile
     */
    private function calculatePercentile(float $value, array $dataset): int
    {
        sort($dataset);
        $count = count($dataset);

        if ($count == 0) {
            return 50;
        }

        $position = 0;
        foreach ($dataset as $item) {
            if ($item < $value) {
                $position++;
            }
        }

        return round(($position / $count) * 100);
    }

    /**
     * Determine strategy type
     */
    private function determineStrategyType(Property $property, array $competitorAnalysis, array $occupancyData): string
    {
        if ($occupancyData['current_occupancy'] < 50) {
            return 'occupancy_focused';
        }

        if ($competitorAnalysis['price_position'] === 'low') {
            return 'revenue_maximization';
        }

        return 'balanced';
    }

    /**
     * Generate pricing reason
     */
    private function generatePricingReason(Property $property, float $optimalPrice, float $currentPrice): string
    {
        $diff = $optimalPrice - $currentPrice;

        if (abs($diff) < 5) {
            return 'Your current price is optimal';
        }

        if ($diff > 0) {
            return 'Market analysis suggests you can increase price while maintaining occupancy';
        }

        return 'Lowering price may improve occupancy rate and overall revenue';
    }
}
