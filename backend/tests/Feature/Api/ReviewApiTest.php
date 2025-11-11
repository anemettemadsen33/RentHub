<?php

namespace Tests\Feature\Api;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\TestHelper;

class ReviewApiTest extends TestCase
{
    use RefreshDatabase, TestHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function guest_can_create_review_for_completed_booking()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available']);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'status' => 'completed',
        ]);

        $reviewData = [
            'property_id' => $property->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Great property, highly recommend!',
        ];

        $response = $this->postJson('/api/v1/reviews', $reviewData);

        $response->assertCreated()
            ->assertJsonStructure([
                'id',
                'rating',
                'comment',
            ]);

        $this->assertDatabaseHas('reviews', [
            'property_id' => $property->id,
            'user_id' => $guest->id,
            'rating' => 5,
        ]);
    }

    /** @test */
    public function it_prevents_review_without_completed_booking()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available']);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'status' => 'confirmed', // Not completed
        ]);

        $reviewData = [
            'property_id' => $property->id,
            'booking_id' => $booking->id,
            'rating' => 5,
            'comment' => 'Great property',
        ];

        $response = $this->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_prevents_duplicate_reviews_for_same_property()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available']);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'status' => 'completed',
        ]);

        // First review
        Review::factory()->create([
            'property_id' => $property->id,
            'user_id' => $guest->id,
            'booking_id' => $booking->id,
        ]);

        // Try second review
        $reviewData = [
            'property_id' => $property->id,
            'rating' => 4,
            'comment' => 'Another review',
        ];

        $response = $this->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'You have already reviewed this property.',
            ]);
    }

    /** @test */
    public function user_can_update_own_review()
    {
        $guest = $this->authenticateGuest();
        $review = Review::factory()->create([
            'user_id' => $guest->id,
            'rating' => 4,
            'comment' => 'Original comment',
        ]);

        $updateData = [
            'rating' => 5,
            'comment' => 'Updated comment - even better!',
        ];

        $response = $this->putJson("/api/v1/reviews/{$review->id}", $updateData);

        $response->assertSuccessful()
            ->assertJsonFragment([
                'rating' => 5,
                'comment' => 'Updated comment - even better!',
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'Updated comment - even better!',
        ]);
    }

    /** @test */
    public function user_cannot_update_others_review()
    {
        $guest = $this->authenticateGuest();
        $otherUser = $this->authenticateUser('tenant');

        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // Re-auth as first guest
        $this->authenticateGuest();

        $updateData = [
            'rating' => 1,
            'comment' => 'Hacked comment',
        ];

        $response = $this->putJson("/api/v1/reviews/{$review->id}", $updateData);

        $response->assertForbidden();
    }

    /** @test */
    public function user_can_delete_own_review()
    {
        $guest = $this->authenticateGuest();
        $review = Review::factory()->create([
            'user_id' => $guest->id,
        ]);

        $response = $this->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertSuccessful();
        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    /** @test */
    public function it_validates_rating_range()
    {
        $guest = $this->authenticateGuest();
        $property = Property::factory()->create(['status' => 'available']);

        $booking = Booking::factory()->create([
            'user_id' => $guest->id,
            'property_id' => $property->id,
            'status' => 'completed',
        ]);

        $reviewData = [
            'property_id' => $property->id,
            'booking_id' => $booking->id,
            'rating' => 6, // Invalid - max is 5
            'comment' => 'Great!',
        ];

        $response = $this->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('rating');
    }

    /** @test */
    public function it_can_list_approved_reviews_for_property()
    {
        $property = Property::factory()->create();

        Review::factory()->count(3)->create([
            'property_id' => $property->id,
            'is_approved' => true,
        ]);

        Review::factory()->count(2)->create([
            'property_id' => $property->id,
            'is_approved' => false,
        ]);

        $response = $this->getJson("/api/v1/reviews?property_id={$property->id}");

        $response->assertSuccessful();

        $data = $response->json('data.data');
        $this->assertCount(3, $data);
    }

    /** @test */
    public function guest_can_view_own_reviews()
    {
        $guest = $this->authenticateGuest();

        Review::factory()->count(5)->create(['user_id' => $guest->id]);
        Review::factory()->count(3)->create(); // Other reviews

        $response = $this->getJson('/api/v1/my-reviews');

        $response->assertSuccessful();

        $data = $response->json('data.data');
        $this->assertCount(5, $data);
    }
}
