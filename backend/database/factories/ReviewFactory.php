<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $rating = fake()->numberBetween(3, 5);
        
        return [
            'property_id' => Property::factory(),
            'user_id' => User::factory(),
            'booking_id' => Booking::factory(),
            'rating' => $rating,
            'comment' => fake()->paragraph(3),
            'cleanliness_rating' => fake()->numberBetween(3, 5),
            'communication_rating' => fake()->numberBetween(3, 5),
            'check_in_rating' => fake()->numberBetween(3, 5),
            'accuracy_rating' => fake()->numberBetween(3, 5),
            'location_rating' => fake()->numberBetween(3, 5),
            'value_rating' => fake()->numberBetween(3, 5),
            'is_approved' => true,
            'admin_notes' => null,
            'owner_response' => null,
            'owner_response_at' => null,
            'photos' => [],
            'helpful_count' => fake()->numberBetween(0, 20),
        ];
    }

    /**
     * Indicate that the review is not approved.
     */
    public function unapproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_approved' => false,
        ]);
    }

    /**
     * Indicate that the review has an owner response.
     */
    public function withResponse(): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_response' => fake()->paragraph(),
            'owner_response_at' => now(),
        ]);
    }
}
