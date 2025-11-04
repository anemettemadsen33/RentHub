<?php

namespace Tests\Feature;

use App\Models\GuestReference;
use App\Models\GuestVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuestVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Storage::fake('public');
    }

    public function test_guest_can_view_verification_status()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/guest-verification');

        $response->assertOk()
            ->assertJsonStructure(['status', 'message']);
    }

    public function test_guest_can_submit_identity_documents()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/guest-verification/identity', [
                'document_type' => 'passport',
                'document_number' => 'AB123456',
                'document_front' => UploadedFile::fake()->image('front.jpg'),
                'selfie_photo' => UploadedFile::fake()->image('selfie.jpg'),
                'document_expiry_date' => now()->addYears(2)->format('Y-m-d'),
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'verification']);

        $this->assertDatabaseHas('guest_verifications', [
            'user_id' => $this->user->id,
            'identity_status' => 'pending',
            'document_type' => 'passport',
        ]);
    }

    public function test_guest_can_add_reference()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/guest-verification/references', [
                'reference_name' => 'John Doe',
                'reference_email' => 'john@example.com',
                'reference_type' => 'previous_landlord',
                'relationship' => 'Previous landlord',
            ]);

        $response->assertOk()
            ->assertJsonStructure(['message', 'reference']);

        $this->assertDatabaseHas('guest_references', [
            'guest_verification_id' => $verification->id,
            'reference_name' => 'John Doe',
        ]);
    }

    public function test_guest_cannot_add_more_than_5_references()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Create 5 references
        GuestReference::factory()->count(5)->create([
            'guest_verification_id' => $verification->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/guest-verification/references', [
                'reference_name' => 'John Doe',
                'reference_email' => 'john@example.com',
                'reference_type' => 'personal',
            ]);

        $response->assertStatus(422);
    }

    public function test_guest_can_request_credit_check()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/guest-verification/credit-check');

        $response->assertOk();

        $this->assertDatabaseHas('guest_verifications', [
            'user_id' => $this->user->id,
            'credit_check_enabled' => true,
            'credit_status' => 'pending',
        ]);
    }

    public function test_guest_can_view_statistics()
    {
        GuestVerification::factory()->create([
            'user_id' => $this->user->id,
            'identity_status' => 'verified',
            'trust_score' => 4.5,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/guest-verification/statistics');

        $response->assertOk()
            ->assertJsonStructure([
                'trust_score',
                'completed_bookings',
                'verification_level',
                'identity_verified',
            ]);
    }

    public function test_trust_score_calculation()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
            'identity_status' => 'verified',
            'background_status' => 'clear',
            'credit_status' => 'approved',
            'completed_bookings' => 5,
            'positive_reviews' => 4,
            'negative_reviews' => 0,
        ]);

        $verification->updateTrustScore();

        $this->assertGreaterThan(4.0, $verification->fresh()->trust_score);
    }

    public function test_fully_verified_guest()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
            'identity_status' => 'verified',
            'background_status' => 'clear',
            'credit_status' => 'approved',
        ]);

        $this->assertTrue($verification->isFullyVerified());
    }

    public function test_guest_can_book_with_verified_identity()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
            'identity_status' => 'verified',
            'trust_score' => 3.5,
        ]);

        $this->assertTrue($verification->canBook());
    }

    public function test_reference_can_verify_with_token()
    {
        $verification = GuestVerification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $reference = GuestReference::factory()->create([
            'guest_verification_id' => $verification->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/guest-verification/references/{$reference->verification_token}/verify", [
            'rating' => 5,
            'comments' => 'Excellent tenant!',
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('guest_references', [
            'id' => $reference->id,
            'status' => 'verified',
            'rating' => 5,
        ]);
    }
}
