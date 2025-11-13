<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Property;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentProof;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentProofTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $host;
    protected $property;
    protected $booking;
    protected $payment;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Create guest user
        $this->user = User::factory()->create([
            'role' => 'guest',
            'email_verified_at' => now(),
        ]);

        // Create host user
        $this->host = User::factory()->create([
            'role' => 'host',
            'email_verified_at' => now(),
        ]);

        // Create property
        $this->property = Property::factory()->create([
            'user_id' => $this->host->id,
            'status' => 'active',
        ]);

        // Create booking
        $this->booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Create payment
        $this->payment = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => $this->booking->id,
            'amount' => 1000.00,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function guest_can_upload_payment_proof()
    {
        $file = UploadedFile::fake()->create('receipt.pdf', 1024);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$this->payment->id}/upload-proof", [
                'proof' => $file,
                'transfer_reference' => 'TRF123456789',
                'transfer_date' => now()->format('Y-m-d'),
                'notes' => 'Transfer realizat astăzi',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'proof' => ['id', 'payment_id', 'file_path', 'status'],
                'payment',
            ]);

        $this->assertDatabaseHas('payment_proofs', [
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('payments', [
            'id' => $this->payment->id,
            'transfer_reference' => 'TRF123456789',
        ]);

        // Verify file was stored
        $proof = PaymentProof::where('payment_id', $this->payment->id)->first();
        Storage::disk('public')->assertExists($proof->file_path);
    }

    /** @test */
    public function guest_cannot_upload_proof_for_other_user_payment()
    {
        $otherUser = User::factory()->create(['role' => 'guest']);
        $file = UploadedFile::fake()->create('receipt.pdf', 1024);

        $response = $this->actingAs($otherUser, 'sanctum')
            ->postJson("/api/v1/payments/{$this->payment->id}/upload-proof", [
                'proof' => $file,
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function upload_validates_file_type()
    {
        $file = UploadedFile::fake()->create('receipt.txt', 100);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$this->payment->id}/upload-proof", [
                'proof' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('proof');
    }

    /** @test */
    public function upload_validates_file_size()
    {
        $file = UploadedFile::fake()->create('receipt.pdf', 15000); // 15MB

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/payments/{$this->payment->id}/upload-proof", [
                'proof' => $file,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('proof');
    }

    /** @test */
    public function host_can_verify_payment_proof()
    {
        $proof = PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/payment-proofs/{$proof->id}/verify", [
                'action' => 'verify',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment verified successfully',
            ]);

        $proof->refresh();
        $this->assertEquals('verified', $proof->status);
        $this->assertNotNull($proof->verified_at);
        $this->assertEquals($this->host->id, $proof->verified_by);

        $this->payment->refresh();
        $this->assertEquals('completed', $this->payment->status);
    }

    /** @test */
    public function host_can_reject_payment_proof()
    {
        $proof = PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/payment-proofs/{$proof->id}/verify", [
                'action' => 'reject',
                'rejection_reason' => 'Suma incorectă în dovadă',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Payment proof rejected',
            ]);

        $proof->refresh();
        $this->assertEquals('rejected', $proof->status);
        $this->assertEquals('Suma incorectă în dovadă', $proof->rejection_reason);
        $this->assertEquals($this->host->id, $proof->verified_by);
    }

    /** @test */
    public function only_property_host_can_verify_proof()
    {
        $otherHost = User::factory()->create(['role' => 'host']);
        $proof = PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($otherHost, 'sanctum')
            ->postJson("/api/v1/payment-proofs/{$proof->id}/verify", [
                'action' => 'verify',
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function rejection_requires_reason()
    {
        $proof = PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->postJson("/api/v1/payment-proofs/{$proof->id}/verify", [
                'action' => 'reject',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('rejection_reason');
    }

    /** @test */
    public function can_download_payment_proof()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('receipt.pdf', 1024);
        $path = $file->store('payment-proofs', 'public');

        $proof = PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'file_path' => $path,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->get("/api/v1/payment-proofs/{$proof->id}/download");

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function host_can_get_pending_proofs()
    {
        // Create multiple payments with proofs
        $payment2 = Payment::factory()->create([
            'user_id' => $this->user->id,
            'booking_id' => Booking::factory()->create([
                'property_id' => $this->property->id,
                'user_id' => $this->user->id,
            ])->id,
        ]);

        PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'pending',
        ]);

        PaymentProof::factory()->create([
            'payment_id' => $payment2->id,
            'status' => 'pending',
        ]);

        // Create verified proof (should not appear)
        PaymentProof::factory()->create([
            'payment_id' => $this->payment->id,
            'status' => 'verified',
        ]);

        $response = $this->actingAs($this->host, 'sanctum')
            ->getJson('/api/v1/payment-proofs/pending');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'proofs',
                'total',
            ])
            ->assertJsonCount(2, 'proofs');
    }
}
