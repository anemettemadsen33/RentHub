<?php

namespace Tests\Unit;

use App\Models\Property;
use App\Models\PricingRule;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PricingService $pricingService;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pricingService = new PricingService();
        $this->property = Property::factory()->create([
            'price_per_night' => 100,
        ]);
    }

    public function test_calculates_base_price_for_date()
    {
        $date = Carbon::now()->addDays(7);
        
        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        $this->assertEquals(100, $price);
    }

    public function test_calculates_total_price_for_date_range()
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = Carbon::now()->addDays(10);

        $result = $this->pricingService->calculateTotalPrice($this->property, $checkIn, $checkOut);

        $this->assertEquals(300, $result['total']);
        $this->assertCount(3, $result['daily_prices']);
    }

    public function test_applies_weekend_pricing_rule()
    {
        // Find next Saturday
        $saturday = Carbon::now()->next(Carbon::SATURDAY);
        
        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'type' => 'weekend',
            'adjustment_type' => 'percentage',
            'adjustment_value' => 50, // 50% increase
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $saturday);

        $this->assertEquals(150, $price); // $100 + 50%
    }

    public function test_applies_seasonal_pricing_rule()
    {
        $date = Carbon::parse('2025-12-25'); // Christmas
        
        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'type' => 'seasonal',
            'adjustment_type' => 'percentage',
            'adjustment_value' => 100, // Double price
            'start_date' => '2025-12-20',
            'end_date' => '2025-12-31',
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        $this->assertEquals(200, $price); // $100 + 100%
    }

    public function test_applies_minimum_stay_discount()
    {
        $checkIn = Carbon::now()->addDays(7);
        $checkOut = Carbon::now()->addDays(14); // 7 nights

        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'type' => 'minimum_stay',
            'minimum_nights' => 7,
            'adjustment_type' => 'percentage',
            'adjustment_value' => -10, // 10% discount
            'is_active' => true,
        ]);

        $result = $this->pricingService->calculateTotalPrice($this->property, $checkIn, $checkOut);

        $this->assertEquals(630, $result['total']); // 7 nights * $90
    }

    public function test_applies_multiple_rules_by_priority()
    {
        $date = Carbon::now()->next(Carbon::SATURDAY);

        // Weekend rule (priority 10)
        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'type' => 'weekend',
            'adjustment_type' => 'percentage',
            'adjustment_value' => 50,
            'priority' => 10,
            'is_active' => true,
        ]);

        // Last minute discount (priority 20)
        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'type' => 'last_minute',
            'adjustment_type' => 'percentage',
            'adjustment_value' => -20,
            'priority' => 20,
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        // Should apply both rules: ($100 + 50%) - 20% = $150 - 20% = $120
        $this->assertEquals(120, $price);
    }

    public function test_rounds_price_to_two_decimals()
    {
        $date = Carbon::now()->addDays(7);

        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'adjustment_type' => 'percentage',
            'adjustment_value' => 33.333,
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        $this->assertIsFloat($price);
        $this->assertEquals(round($price, 2), $price);
    }

    public function test_ignores_inactive_pricing_rules()
    {
        $date = Carbon::now()->addDays(7);

        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'adjustment_type' => 'percentage',
            'adjustment_value' => 50,
            'is_active' => false,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        $this->assertEquals(100, $price); // No change
    }

    public function test_handles_fixed_amount_adjustments()
    {
        $date = Carbon::now()->addDays(7);

        PricingRule::factory()->create([
            'property_id' => $this->property->id,
            'adjustment_type' => 'fixed',
            'adjustment_value' => 25,
            'is_active' => true,
        ]);

        $price = $this->pricingService->calculatePriceForDate($this->property, $date);

        $this->assertEquals(125, $price); // $100 + $25
    }

    public function test_daily_prices_array_contains_correct_dates()
    {
        $checkIn = Carbon::parse('2025-12-01');
        $checkOut = Carbon::parse('2025-12-04');

        $result = $this->pricingService->calculateTotalPrice($this->property, $checkIn, $checkOut);

        $this->assertArrayHasKey('2025-12-01', $result['daily_prices']);
        $this->assertArrayHasKey('2025-12-02', $result['daily_prices']);
        $this->assertArrayHasKey('2025-12-03', $result['daily_prices']);
        $this->assertArrayNotHasKey('2025-12-04', $result['daily_prices']); // Checkout day not included
    }
}
