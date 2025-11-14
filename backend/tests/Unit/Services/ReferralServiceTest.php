<?php

namespace Tests\Unit\Services;

use App\Services\ReferralService;
use App\Models\User;
use App\Models\Referral;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralServiceTest extends TestCase
{
    use RefreshDatabase;

    private ReferralService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ReferralService::class);
    }

    public function test_can_generate_referral_code_for_user(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act
        $code = $this->service->generateReferralCode($user->id);

        // Assert
        $this->assertNotEmpty($code);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $user->id,
            'code' => $code,
        ]);
    }

    public function test_referral_code_is_unique(): void
    {
        // Arrange
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Act
        $code1 = $this->service->generateReferralCode($user1->id);
        $code2 = $this->service->generateReferralCode($user2->id);

        // Assert
        $this->assertNotEquals($code1, $code2);
    }

    public function test_can_validate_referral_code(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $code = $this->service->generateReferralCode($referrer->id);

        // Act
        $isValid = $this->service->validateCode($code);

        // Assert
        $this->assertTrue($isValid);
    }

    public function test_invalid_referral_code_returns_false(): void
    {
        // Act
        $isValid = $this->service->validateCode('INVALID_CODE');

        // Assert
        $this->assertFalse($isValid);
    }

    public function test_can_process_successful_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        $code = $this->service->generateReferralCode($referrer->id);

        // Act
        $result = $this->service->processReferral($code, $referred->id);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('referrals', [
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'code' => $code,
            'status' => 'completed',
        ]);
    }

    public function test_cannot_use_same_referral_code_twice(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred1 = User::factory()->create();
        $referred2 = User::factory()->create();
        $code = $this->service->generateReferralCode($referrer->id);

        // Act
        $this->service->processReferral($code, $referred1->id);
        $result = $this->service->processReferral($code, $referred2->id);

        // Assert
        $this->assertFalse($result);
    }

    public function test_can_get_referral_stats_for_user(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $code = $this->service->generateReferralCode($referrer->id);
        
        $referred1 = User::factory()->create();
        $referred2 = User::factory()->create();
        
        $this->service->processReferral($code, $referred1->id);
        
        $code2 = $this->service->generateReferralCode($referrer->id);
        $this->service->processReferral($code2, $referred2->id);

        // Act
        $stats = $this->service->getReferralStats($referrer->id);

        // Assert
        $this->assertEquals(2, $stats['total_referrals']);
        $this->assertGreaterThan(0, $stats['total_earnings'] ?? 0);
    }

    public function test_referrer_receives_reward_for_successful_referral(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        $referred = User::factory()->create();
        $code = $this->service->generateReferralCode($referrer->id);

        // Act
        $this->service->processReferral($code, $referred->id);
        $this->service->awardReferralBonus($code);

        // Assert
        $this->assertDatabaseHas('referrals', [
            'code' => $code,
            'bonus_awarded' => true,
        ]);
    }

    public function test_can_get_user_referral_history(): void
    {
        // Arrange
        $referrer = User::factory()->create();
        Referral::factory()->count(3)->create([
            'referrer_id' => $referrer->id,
            'status' => 'completed',
        ]);

        // Act
        $history = $this->service->getUserReferralHistory($referrer->id);

        // Assert
        $this->assertCount(3, $history);
    }
}
