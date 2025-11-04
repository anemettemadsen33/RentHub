<?php

namespace App\Services;

use App\Models\Property;
use Carbon\Carbon;

class SmartPricingService
{
    public function calculateDynamicPrice(Property $property, Carbon $date): float
    {
        $basePrice = $property->price_per_night;
        $multiplier = 1.0;

        // Weekend pricing (Friday, Saturday)
        if (in_array($date->dayOfWeek, [5, 6])) {
            $multiplier *= 1.3; // 30% increase
        }

        // Holiday pricing
        if ($this->isHoliday($date)) {
            $multiplier *= 1.5; // 50% increase
        }

        // Seasonal pricing
        $season = $this->getSeason($date);
        switch ($season) {
            case 'peak':
                $multiplier *= 1.4;
                break;
            case 'high':
                $multiplier *= 1.2;
                break;
            case 'low':
                $multiplier *= 0.8;
                break;
        }

        // Occupancy-based pricing
        $occupancyRate = $this->getOccupancyRate($property, $date);
        if ($occupancyRate > 0.8) {
            $multiplier *= 1.2; // High demand
        } elseif ($occupancyRate < 0.3) {
            $multiplier *= 0.9; // Low demand
        }

        // Last-minute discount (within 3 days)
        $daysUntil = now()->diffInDays($date, false);
        if ($daysUntil <= 3 && $daysUntil > 0 && $occupancyRate < 0.5) {
            $multiplier *= 0.85; // 15% discount
        }

        return round($basePrice * $multiplier, 2);
    }

    private function isHoliday(Carbon $date): bool
    {
        $holidays = [
            '12-25', '12-26', // Christmas
            '01-01', // New Year
            '07-04', // Independence Day
            // Add more holidays
        ];

        return in_array($date->format('m-d'), $holidays);
    }

    private function getSeason(Carbon $date): string
    {
        $month = $date->month;

        if (in_array($month, [6, 7, 8, 12])) {
            return 'peak'; // Summer & December
        } elseif (in_array($month, [4, 5, 9, 10])) {
            return 'high'; // Spring & Fall
        }

        return 'low'; // Winter months
    }

    private function getOccupancyRate(Property $property, Carbon $date): float
    {
        $startDate = $date->copy()->startOfMonth();
        $endDate = $date->copy()->endOfMonth();
        $daysInMonth = $startDate->daysInMonth;

        $bookedDays = $property->bookings()
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('check_in_date', [$startDate, $endDate])
                  ->orWhereBetween('check_out_date', [$startDate, $endDate]);
            })
            ->count();

        return $bookedDays / $daysInMonth;
    }

    public function suggestOptimalPrice(Property $property, Carbon $date): array
    {
        $dynamicPrice = $this->calculateDynamicPrice($property, $date);
        $competitorAvgPrice = $this->getCompetitorAveragePrice($property);

        return [
            'suggested_price' => $dynamicPrice,
            'base_price' => $property->price_per_night,
            'competitor_avg' => $competitorAvgPrice,
            'price_difference' => $dynamicPrice - $competitorAvgPrice,
            'recommendation' => $dynamicPrice < $competitorAvgPrice ? 'competitive' : 'premium',
        ];
    }

    private function getCompetitorAveragePrice(Property $property): float
    {
        return Property::where('city', $property->city)
            ->where('property_type', $property->property_type)
            ->where('id', '!=', $property->id)
            ->where('status', 'published')
            ->avg('price_per_night') ?? $property->price_per_night;
    }
}
