<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavedSearch>
 */
class SavedSearchFactory extends Factory
{
    protected $model = SavedSearch::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'criteria' => json_encode([
                'location' => fake()->city(),
                'min_price' => fake()->numberBetween(50, 100),
                'max_price' => fake()->numberBetween(200, 500),
                'bedrooms' => fake()->numberBetween(1, 3),
                'property_type' => fake()->randomElement(['apartment', 'house', 'condo']),
            ]),
            'alert_frequency' => fake()->randomElement(['instant', 'daily', 'weekly']),
            'enable_alerts' => true,
            'is_active' => true,
        ];
    }

    /**
     * Indicate that alerts are disabled for this search.
     */
    public function withoutAlerts(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_alerts' => false,
        ]);
    }

    /**
     * Indicate that the search is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
