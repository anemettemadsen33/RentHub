<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Property;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Property $property;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
        ]);
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'status' => 'completed',
        ]);
        Storage::fake('public');
    }

    public function test_user_can_create_review()
    {
        $reviewData = [
            'booking_id' => $this->booking->id,
            'property_id' => $this->property->id,
            'rating' => 5,
            'comment' => 'Excellent property!',
            'cleanliness_rating' => 5,
            'accuracy_rating' => 5,
            'communication_rating' => 5,
            'location_rating' => 5,
            'check_in_rating' => 5,
            'value_rating' => 5,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reviews', $reviewData);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'rating', 'comment']);

        $this->assertDatabaseHas('reviews', [
            'property_id' => $this->property->id,
            'user_id' => $this->user->id,
            'rating' => 5,
        ]);
    }

    public function test_user_can_view_property_reviews()
    {
        Review::factory()->count(3)->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->getJson("/api/v1/properties/{$this->property->id}/reviews");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_update_their_review()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/reviews/{$review->id}", [
                'rating' => 4,
                'comment' => 'Updated review',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => 'Updated review',
        ]);
    }

    public function test_user_can_delete_their_review()
    {
        $review = Review::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/reviews/{$review->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }

    public function test_user_can_upload_photos_with_review()
    {
        $reviewData = [
            'booking_id' => $this->booking->id,
            'property_id' => $this->property->id,
            'rating' => 5,
            'comment' => 'Great place!',
            'photos' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reviews', $reviewData);

        $response->assertCreated();
    }

    public function test_owner_can_respond_to_review()
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/respond", [
                'response' => 'Thank you for your feedback!',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('review_responses', [
            'review_id' => $review->id,
            'response' => 'Thank you for your feedback!',
        ]);
    }

    public function test_user_cannot_review_without_completed_booking()
    {
        $newProperty = Property::factory()->create();

        $reviewData = [
            'property_id' => $newProperty->id,
            'rating' => 5,
            'comment' => 'Great property!',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422);
    }

    public function test_user_cannot_review_same_property_twice()
    {
        Review::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'booking_id' => $this->booking->id,
        ]);

        $reviewData = [
            'booking_id' => $this->booking->id,
            'property_id' => $this->property->id,
            'rating' => 5,
            'comment' => 'Another review',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422);
    }

    public function test_rating_must_be_between_1_and_5()
    {
        $reviewData = [
            'booking_id' => $this->booking->id,
            'property_id' => $this->property->id,
            'rating' => 6,
            'comment' => 'Invalid rating',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/reviews', $reviewData);

        $response->assertStatus(422);
    }

    public function test_user_can_mark_review_as_helpful()
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/helpful");

        $response->assertOk();

        $this->assertDatabaseHas('review_helpful_votes', [
            'review_id' => $review->id,
            'user_id' => $this->user->id,
        ]);
    }

    public function test_property_average_rating_is_calculated()
    {
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 5,
        ]);
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 3,
        ]);

        $response = $this->getJson("/api/v1/properties/{$this->property->id}");

        $response->assertOk()
            ->assertJsonFragment(['average_rating' => 4.0]);
    }
}
