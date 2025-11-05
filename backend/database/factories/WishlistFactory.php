<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Wishlist>
 */
class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_public' => fake()->boolean(30),
            'share_token' => null,
        ];
    }

    /**
     * Indicate that the wishlist is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => true,
        ]);
    }

    /**
     * Indicate that the wishlist is the default wishlist.
     */
    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Favorites',
            'is_default' => true,
        ]);
    }
}
