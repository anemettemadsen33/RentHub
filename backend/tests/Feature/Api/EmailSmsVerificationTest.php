<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\SendGridService;
use App\Services\TwilioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailSmsVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => null,
            'phone' => null,
            'phone_verified_at' => null,
        ]);
    }

    /** @test */
    public function user_can_request_email_verification_code()
    {
        // Mock SendGrid service
        $this->mock(SendGridService::class, function ($mock) {
            $mock->shouldReceive('sendVerificationCode')
                ->once()
                ->andReturn(true);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/send', [
                'email' => 'newemail@example.com'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cod de verificare trimis la newemail@example.com'
            ]);

        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $this->user->id,
            'type' => 'email',
            'contact' => 'newemail@example.com',
        ]);
    }

    /** @test */
    public function user_cannot_verify_with_invalid_code()
    {
        VerificationCode::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'code' => '123456',
            'contact' => 'test@example.com',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/verify', [
                'code' => '999999' // Wrong code
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Cod incorect'
            ]);

        $this->assertNull($this->user->fresh()->email_verified_at);
    }

    /** @test */
    public function user_can_verify_email_with_correct_code()
    {
        $this->mock(SendGridService::class, function ($mock) {
            $mock->shouldReceive('sendWelcomeEmail')
                ->once()
                ->andReturn(true);
        });

        $code = '123456';
        VerificationCode::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'code' => $code,
            'contact' => 'verified@example.com',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/verify', [
                'code' => $code
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Email verificat cu succes!'
            ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->email_verified_at);
        $this->assertEquals('verified@example.com', $this->user->email);
    }

    /** @test */
    public function verification_code_expires_after_15_minutes()
    {
        $code = '123456';
        VerificationCode::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'code' => $code,
            'contact' => 'test@example.com',
            'expires_at' => now()->subMinutes(1), // Expired
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/verify', [
                'code' => $code
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Codul a expirat. Te rugăm să soliciti unul nou.'
            ]);
    }

    /** @test */
    public function rate_limiting_prevents_spam()
    {
        $this->mock(SendGridService::class, function ($mock) {
            $mock->shouldReceive('sendVerificationCode')
                ->times(3)
                ->andReturn(true);
        });

        // Send 3 codes (max allowed in 5 minutes)
        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->user, 'sanctum')
                ->postJson('/api/v1/verification/email/send', [
                    'email' => 'test@example.com'
                ])
                ->assertStatus(200);
        }

        // 4th attempt should be blocked
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/send', [
                'email' => 'test@example.com'
            ]);

        $response->assertStatus(429)
            ->assertJson([
                'success' => false,
                'message' => 'Prea multe cereri. Te rugăm să aștepți 5 minute.'
            ]);
    }

    /** @test */
    public function user_can_request_sms_verification_code()
    {
        $this->mock(TwilioService::class, function ($mock) {
            $mock->shouldReceive('formatPhoneNumber')
                ->with('0712345678')
                ->andReturn('+40712345678');

            $mock->shouldReceive('sendVerificationCode')
                ->once()
                ->andReturn(true);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/sms/send', [
                'phone' => '0712345678'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cod de verificare trimis la +40712345678'
            ]);

        $this->assertDatabaseHas('verification_codes', [
            'user_id' => $this->user->id,
            'type' => 'sms',
            'contact' => '+40712345678',
        ]);
    }

    /** @test */
    public function user_can_verify_phone_with_correct_code()
    {
        $code = '123456';
        VerificationCode::create([
            'user_id' => $this->user->id,
            'type' => 'sms',
            'code' => $code,
            'contact' => '+40712345678',
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/sms/verify', [
                'code' => $code
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Telefon verificat cu succes!'
            ]);

        $this->user->refresh();
        $this->assertNotNull($this->user->phone_verified_at);
        $this->assertEquals('+40712345678', $this->user->phone);
    }

    /** @test */
    public function max_attempts_blocks_further_verification()
    {
        $verification = VerificationCode::create([
            'user_id' => $this->user->id,
            'type' => 'email',
            'code' => '123456',
            'contact' => 'test@example.com',
            'expires_at' => now()->addMinutes(15),
            'attempts' => 5, // Max attempts reached
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/email/verify', [
                'code' => '123456'
            ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Prea multe încercări. Te rugăm să soliciti un cod nou.'
            ]);
    }

    /** @test */
    public function invalid_phone_number_format_is_rejected()
    {
        $this->mock(TwilioService::class, function ($mock) {
            $mock->shouldReceive('formatPhoneNumber')
                ->with('invalid')
                ->andReturn(null);
        });

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/verification/sms/send', [
                'phone' => 'invalid'
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Număr de telefon invalid. Folosește formatul: +40XXXXXXXXX'
            ]);
    }
}
