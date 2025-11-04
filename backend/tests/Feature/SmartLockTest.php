<?php

namespace Tests\Feature;

use App\Models\AccessCode;
use App\Models\Booking;
use App\Models\Property;
use App\Models\SmartLock;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartLockTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $owner;
    protected Property $property;
    protected SmartLock $smartLock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
        ]);
        $this->smartLock = SmartLock::factory()->create([
            'property_id' => $this->property->id,
            'provider' => 'mock',
        ]);
    }

    public function test_owner_can_create_smart_lock()
    {
        $lockData = [
            'property_id' => $this->property->id,
            'name' => 'Front Door Lock',
            'provider' => 'mock',
            'device_id' => 'LOCK123',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/smart-locks', $lockData);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'name', 'provider']);

        $this->assertDatabaseHas('smart_locks', [
            'property_id' => $this->property->id,
            'name' => 'Front Door Lock',
        ]);
    }

    public function test_owner_can_view_property_locks()
    {
        SmartLock::factory()->count(3)->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/properties/{$this->property->id}/smart-locks");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_access_code_generated_on_booking_confirmation()
    {
        $booking = Booking::factory()->create([
            'property_id' => $this->property->id,
            'user_id' => $this->user->id,
            'status' => 'pending',
            'check_in' => Carbon::now()->addDays(1),
            'check_out' => Carbon::now()->addDays(3),
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/bookings/{$booking->id}/confirm");

        $response->assertOk();

        $this->assertDatabaseHas('access_codes', [
            'booking_id' => $booking->id,
            'smart_lock_id' => $this->smartLock->id,
        ]);
    }

    public function test_guest_can_view_their_access_code()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
        ]);

        $accessCode = AccessCode::factory()->create([
            'booking_id' => $booking->id,
            'smart_lock_id' => $this->smartLock->id,
            'code' => '123456',
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/bookings/{$booking->id}/access-code");

        $response->assertOk()
            ->assertJsonFragment(['code' => '123456']);
    }

    public function test_owner_can_lock_device()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/smart-locks/{$this->smartLock->id}/lock");

        $response->assertOk()
            ->assertJsonFragment(['status' => 'locked']);

        $this->assertDatabaseHas('lock_activities', [
            'smart_lock_id' => $this->smartLock->id,
            'action' => 'lock',
        ]);
    }

    public function test_owner_can_unlock_device()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/smart-locks/{$this->smartLock->id}/unlock");

        $response->assertOk()
            ->assertJsonFragment(['status' => 'unlocked']);

        $this->assertDatabaseHas('lock_activities', [
            'smart_lock_id' => $this->smartLock->id,
            'action' => 'unlock',
        ]);
    }

    public function test_owner_can_view_lock_activity()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/smart-locks/{$this->smartLock->id}/activity");

        $response->assertOk()
            ->assertJsonStructure(['data']);
    }

    public function test_access_code_is_time_limited()
    {
        $booking = Booking::factory()->create([
            'user_id' => $this->user->id,
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->addDays(1),
            'check_out' => Carbon::now()->addDays(3),
        ]);

        $accessCode = AccessCode::factory()->create([
            'booking_id' => $booking->id,
            'smart_lock_id' => $this->smartLock->id,
            'valid_from' => Carbon::now()->addDays(1),
            'valid_until' => Carbon::now()->addDays(3),
        ]);

        $this->assertTrue($accessCode->valid_from->isFuture());
        $this->assertTrue($accessCode->valid_until->isFuture());
    }

    public function test_guest_cannot_access_other_guest_code()
    {
        $otherUser = User::factory()->create();
        $booking = Booking::factory()->create([
            'user_id' => $otherUser->id,
            'property_id' => $this->property->id,
        ]);

        $accessCode = AccessCode::factory()->create([
            'booking_id' => $booking->id,
            'smart_lock_id' => $this->smartLock->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/bookings/{$booking->id}/access-code");

        $response->assertStatus(403);
    }

    public function test_owner_can_manually_create_access_code()
    {
        $codeData = [
            'smart_lock_id' => $this->smartLock->id,
            'code' => '789012',
            'valid_from' => Carbon::now()->format('Y-m-d H:i:s'),
            'valid_until' => Carbon::now()->addDays(7)->format('Y-m-d H:i:s'),
            'name' => 'Maintenance Staff',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/access-codes', $codeData);

        $response->assertCreated();

        $this->assertDatabaseHas('access_codes', [
            'smart_lock_id' => $this->smartLock->id,
            'code' => '789012',
        ]);
    }

    public function test_owner_can_delete_access_code()
    {
        $accessCode = AccessCode::factory()->create([
            'smart_lock_id' => $this->smartLock->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/access-codes/{$accessCode->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('access_codes', ['id' => $accessCode->id]);
    }
}
