<?php

namespace Tests\Feature\Api;

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

    protected User $guest;
    protected User $host;
    protected Property $property;
    protected Booking $booking;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test users
        $this->guest = User::factory()->create(['role' => 'guest']);
        $this->host = User::factory()->create(['role' => 'host']);
        
        // Create property
        $this->property = Property::factory()->create([
            'user_id' => $this->host->id,
            'status' => 'active',
        ]);
        
        // Create completed booking
        $this->booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'status' => 'completed',
        ]);
    }

    public function test_guest_can_create_review_after_completed_booking(): void
    {
        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'property_id' => $this->property->id,
                'booking_id' => $this->booking->id,
                'rating' => 5,
                'comment' => 'Amazing property! Highly recommended.',
                'cleanliness_rating' => 5,
                'communication_rating' => 5,
                'check_in_rating' => 4,
                'accuracy_rating' => 5,
                'location_rating' => 5,
                'value_rating' => 4,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('reviews', [
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'booking_id' => $this->booking->id,
            'rating' => 5,
            'cleanliness_rating' => 5,
        ]);
    }

    public function test_guest_cannot_review_without_completed_booking(): void
    {
        $pendingBooking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->guest->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'property_id' => $this->property->id,
                'booking_id' => $pendingBooking->id,
                'rating' => 5,
                'comment' => 'Great!',
            ]);

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'You must complete the booking before reviewing this property.',
            ]);
    }

    public function test_validates_rating_range(): void
    {
        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'property_id' => $this->property->id,
                'booking_id' => $this->booking->id,
                'rating' => 6, // Invalid: max is 5
                'comment' => 'Test',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating']);
    }

    public function test_validates_photo_upload_limits(): void
    {
        Storage::fake('public');

        $photos = [
            UploadedFile::fake()->image('photo1.jpg'),
            UploadedFile::fake()->image('photo2.jpg'),
            UploadedFile::fake()->image('photo3.jpg'),
            UploadedFile::fake()->image('photo4.jpg'),
            UploadedFile::fake()->image('photo5.jpg'),
            UploadedFile::fake()->image('photo6.jpg'), // 6th photo should fail (max 5)
        ];

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson('/api/v1/reviews', [
                'property_id' => $this->property->id,
                'booking_id' => $this->booking->id,
                'rating' => 5,
                'photos' => $photos,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['photos']);
    }

    public function test_can_get_reviews_for_property(): void
    {
        // Create multiple reviews
        Review::factory()->count(3)->create([
            'property_id' => $this->property->id,
            'is_approved' => true,
        ]);

        $response = $this->getJson("/api/v1/reviews?property_id={$this->property->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'data' => [
                        '*' => ['id', 'rating', 'comment', 'user', 'created_at'],
                    ],
                ],
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_can_filter_reviews_by_rating(): void
    {
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 5,
            'is_approved' => true,
        ]);
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 3,
            'is_approved' => true,
        ]);

        $response = $this->getJson("/api/v1/reviews?property_id={$this->property->id}&min_rating=4");

        $response->assertStatus(200);
        $reviews = $response->json('data.data');
        
        $this->assertCount(1, $reviews);
        $this->assertEquals(5, $reviews[0]['rating']);
    }

    public function test_can_sort_reviews_by_helpful_count(): void
    {
        $review1 = Review::factory()->create([
            'property_id' => $this->property->id,
            'helpful_count' => 5,
            'is_approved' => true,
        ]);
        $review2 = Review::factory()->create([
            'property_id' => $this->property->id,
            'helpful_count' => 10,
            'is_approved' => true,
        ]);

        $response = $this->getJson("/api/v1/reviews?property_id={$this->property->id}&sort_by=helpful&sort_order=desc");

        $response->assertStatus(200);
        $reviews = $response->json('data.data');
        
        $this->assertEquals($review2->id, $reviews[0]['id']);
        $this->assertEquals($review1->id, $reviews[1]['id']);
    }

    public function test_host_can_respond_to_review(): void
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/respond", [
                'response' => 'Thank you for your feedback! We appreciate your stay.',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'owner_response' => 'Thank you for your feedback! We appreciate your stay.',
        ]);

        $review->refresh();
        $this->assertNotNull($review->owner_response_at);
    }

    public function test_only_property_owner_can_respond_to_review(): void
    {
        $otherHost = User::factory()->create(['role' => 'host']);
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($otherHost, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/respond", [
                'response' => 'Trying to respond',
            ]);

        $response->assertStatus(403);
    }

    public function test_can_mark_review_as_helpful(): void
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => true,
            'helpful_count' => 0,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/helpful");

        $response->assertStatus(200);

        $review->refresh();
        $this->assertEquals(1, $review->helpful_count);
    }

    public function test_cannot_mark_review_helpful_twice(): void
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => true,
        ]);

        // First vote
        $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/helpful");

        // Second vote (should fail)
        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/helpful");

        $response->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'You have already marked this review as helpful.',
            ]);
    }

    public function test_admin_can_approve_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/approve", [
                'is_approved' => true,
                'admin_notes' => 'Content is appropriate',
            ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'is_approved' => true,
            'admin_notes' => 'Content is appropriate',
        ]);
    }

    public function test_only_admin_can_approve_reviews(): void
    {
        $review = Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => false,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->postJson("/api/v1/reviews/{$review->id}/approve", [
                'is_approved' => true,
            ]);

        $response->assertStatus(403);
    }

    public function test_guest_can_view_their_reviews(): void
    {
        Review::factory()->count(3)->create([
            'user_id' => $this->guest->id,
            'is_approved' => true,
        ]);

        $response = $this->actingAs($this->guest, 'sanctum')
            ->getJson('/api/v1/reviews/my-reviews');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertCount(3, $response->json('data.data'));
    }

    public function test_unapproved_reviews_not_shown_publicly(): void
    {
        Review::factory()->create([
            'property_id' => $this->property->id,
            'is_approved' => false,
        ]);

        $response = $this->getJson("/api/v1/reviews?property_id={$this->property->id}");

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data.data'));
    }

    public function test_property_average_rating_updates_after_review(): void
    {
        // Create multiple reviews
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 5,
            'is_approved' => true,
        ]);
        Review::factory()->create([
            'property_id' => $this->property->id,
            'rating' => 3,
            'is_approved' => true,
        ]);

        $this->property->refresh();
        
        // Average should be (5 + 3) / 2 = 4.0
        $this->assertEquals(4.0, $this->property->average_rating);
        $this->assertEquals(2, $this->property->reviews_count);
    }
}
