<?php

namespace Database\Factories;

use App\Models\PricingRule;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingRule>
 */
class PricingRuleFactory extends Factory
{
    protected $model = PricingRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([
            'seasonal',
            'weekend',
            'holiday',
            'demand',
            'last_minute',
            'early_bird',
            'weekly',
            'monthly',
        ]);

        return [
            'property_id' => Property::factory(),
            'type' => $type,
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'start_date' => now()->addDays(fake()->numberBetween(1, 30)),
            'end_date' => now()->addDays(fake()->numberBetween(31, 90)),
            'days_of_week' => null,
            'adjustment_type' => fake()->randomElement(['percentage', 'fixed']),
            'adjustment_value' => fake()->randomFloat(2, -50, 100),
            'min_nights' => null,
            'max_nights' => null,
            'advance_booking_days' => null,
            'last_minute_days' => null,
            'priority' => fake()->numberBetween(0, 10),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the pricing rule is for weekends.
     */
    public function weekend(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'weekend',
            'days_of_week' => json_encode([0, 6]), // Sunday and Saturday
        ]);
    }

    /**
     * Indicate that the pricing rule is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
