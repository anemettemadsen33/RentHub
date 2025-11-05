<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => Wishlist::factory(),
            'property_id' => Property::factory(),
            'notes' => fake()->sentence(),
            'price_alert' => null,
            'notify_availability' => false,
        ];
    }

    /**
     * Indicate that the item has a price alert.
     */
    public function withPriceAlert(): static
    {
        return $this->state(fn (array $attributes) => [
            'price_alert' => fake()->randomFloat(2, 50, 300),
        ]);
    }

    /**
     * Indicate that availability notifications are enabled.
     */
    public function notifyAvailability(): static
    {
        return $this->state(fn (array $attributes) => [
            'notify_availability' => true,
        ]);
    }
}
