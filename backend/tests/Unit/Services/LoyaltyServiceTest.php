<?php

namespace Tests\Unit\Services;

use App\Services\LoyaltyService;
use App\Models\User;
use App\Models\UserLoyalty;
use App\Models\LoyaltyTier;
use App\Models\LoyaltyTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoyaltyServiceTest extends TestCase
{
    use RefreshDatabase;

    private LoyaltyService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(LoyaltyService::class);
    }

    public function test_can_award_points_to_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        // Act
        $this->service->awardPoints($user->id, 100, 'booking_completed');

        // Assert
        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $user->id,
            'points' => 100,
            'type' => 'booking_completed',
        ]);
    }

    public function test_can_deduct_points_from_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        UserLoyalty::factory()->create([
            'user_id' => $user->id,
            'total_points' => 500,
            'current_points' => 500,
        ]);

        // Act
        $result = $this->service->deductPoints($user->id, 100, 'reward_redeemed');

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('loyalty_transactions', [
            'user_id' => $user->id,
            'points' => -100,
            'type' => 'reward_redeemed',
        ]);
    }

    public function test_cannot_deduct_more_points_than_available(): void
    {
        // Arrange
        $user = User::factory()->create();
        UserLoyalty::factory()->create([
            'user_id' => $user->id,
            'current_points' => 50,
        ]);

        // Act
        $result = $this->service->deductPoints($user->id, 100, 'reward_redeemed');

        // Assert
        $this->assertFalse($result);
    }

    public function test_can_get_user_loyalty_status(): void
    {
        // Arrange
        $user = User::factory()->create();
        $tier = LoyaltyTier::factory()->create([
            'name' => 'Gold',
            'min_points' => 1000,
        ]);
        
        UserLoyalty::factory()->create([
            'user_id' => $user->id,
            'current_points' => 1500,
            'tier_id' => $tier->id,
        ]);

        // Act
        $status = $this->service->getUserLoyaltyStatus($user->id);

        // Assert
        $this->assertNotNull($status);
        $this->assertEquals(1500, $status->current_points);
        $this->assertEquals('Gold', $status->tier->name);
    }

    public function test_user_tier_upgrades_when_points_threshold_reached(): void
    {
        // Arrange
        $user = User::factory()->create();
        
        $bronzeTier = LoyaltyTier::factory()->create([
            'name' => 'Bronze',
            'min_points' => 0,
        ]);
        
        $silverTier = LoyaltyTier::factory()->create([
            'name' => 'Silver',
            'min_points' => 1000,
        ]);

        UserLoyalty::factory()->create([
            'user_id' => $user->id,
            'current_points' => 900,
            'tier_id' => $bronzeTier->id,
        ]);

        // Act
        $this->service->awardPoints($user->id, 200, 'booking_completed');

        // Assert
        $loyalty = UserLoyalty::where('user_id', $user->id)->first();
        $this->assertEquals($silverTier->id, $loyalty->tier_id);
    }

    public function test_can_calculate_points_for_booking_amount(): void
    {
        // Act
        $points = $this->service->calculatePointsForBooking(1000);

        // Assert
        $this->assertGreaterThan(0, $points);
        $this->assertIsInt($points);
    }

    public function test_can_get_available_rewards_for_user(): void
    {
        // Arrange
        $user = User::factory()->create();
        UserLoyalty::factory()->create([
            'user_id' => $user->id,
            'current_points' => 2000,
        ]);

        // Act
        $rewards = $this->service->getAvailableRewards($user->id);

        // Assert
        $this->assertIsArray($rewards);
    }
}
