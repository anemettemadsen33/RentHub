<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    protected $model = Property::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'type' => fake()->randomElement(['apartment', 'house', 'villa', 'condo', 'studio']),
            'bedrooms' => fake()->numberBetween(1, 5),
            'bathrooms' => fake()->numberBetween(1, 3),
            'guests' => fake()->numberBetween(1, 8),
            'price_per_night' => fake()->randomFloat(2, 50, 500),
            'cleaning_fee' => fake()->randomFloat(2, 20, 100),
            'security_deposit' => fake()->randomFloat(2, 100, 1000),
            'street_address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'state' => fake()->state(),
            'country' => fake()->country(),
            'postal_code' => fake()->postcode(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'area_sqm' => fake()->numberBetween(30, 200),
            'built_year' => fake()->numberBetween(1950, 2023),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'available_from' => now(),
            'available_until' => now()->addMonths(12),
            'images' => json_encode([]),
            'main_image' => null,
            'user_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the property is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the property is featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
