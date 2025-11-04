<?php

namespace Tests\Feature;

use App\Models\BlockedDate;
use App\Models\ExternalCalendar;
use App\Models\Property;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $owner;

    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->owner = User::factory()->create(['role' => 'owner']);
        $this->property = Property::factory()->create([
            'owner_id' => $this->owner->id,
        ]);
    }

    public function test_owner_can_block_dates()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/properties/{$this->property->id}/blocked-dates", [
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'reason' => 'Maintenance',
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('blocked_dates', [
            'property_id' => $this->property->id,
            'reason' => 'Maintenance',
        ]);
    }

    public function test_owner_can_view_blocked_dates()
    {
        BlockedDate::factory()->count(3)->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/properties/{$this->property->id}/blocked-dates");

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_owner_can_unblock_dates()
    {
        $blockedDate = BlockedDate::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/blocked-dates/{$blockedDate->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('blocked_dates', ['id' => $blockedDate->id]);
    }

    public function test_guest_cannot_book_blocked_dates()
    {
        BlockedDate::factory()->create([
            'property_id' => $this->property->id,
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(10),
        ]);

        $bookingData = [
            'property_id' => $this->property->id,
            'check_in' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'check_out' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'guests' => 2,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/bookings', $bookingData);

        $response->assertStatus(422);
    }

    public function test_owner_can_sync_external_calendar()
    {
        $calendarData = [
            'property_id' => $this->property->id,
            'name' => 'Airbnb Calendar',
            'ical_url' => 'https://example.com/calendar.ics',
            'provider' => 'airbnb',
        ];

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson('/api/v1/external-calendars', $calendarData);

        $response->assertCreated();

        $this->assertDatabaseHas('external_calendars', [
            'property_id' => $this->property->id,
            'provider' => 'airbnb',
        ]);
    }

    public function test_owner_can_view_external_calendars()
    {
        ExternalCalendar::factory()->count(2)->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->getJson("/api/v1/properties/{$this->property->id}/external-calendars");

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_owner_can_remove_external_calendar()
    {
        $calendar = ExternalCalendar::factory()->create([
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->owner, 'sanctum')
            ->deleteJson("/api/v1/external-calendars/{$calendar->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('external_calendars', ['id' => $calendar->id]);
    }

    public function test_property_availability_calendar()
    {
        $response = $this->getJson("/api/v1/properties/{$this->property->id}/availability");

        $response->assertOk()
            ->assertJsonStructure(['available_dates', 'blocked_dates']);
    }

    public function test_cannot_block_past_dates()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/properties/{$this->property->id}/blocked-dates", [
                'start_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'reason' => 'Test',
            ]);

        $response->assertStatus(422);
    }

    public function test_end_date_must_be_after_start_date()
    {
        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/properties/{$this->property->id}/blocked-dates", [
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'reason' => 'Test',
            ]);

        $response->assertStatus(422);
    }

    public function test_owner_cannot_block_dates_for_other_property()
    {
        $otherProperty = Property::factory()->create();

        $response = $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/properties/{$otherProperty->id}/blocked-dates", [
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'reason' => 'Test',
            ]);

        $response->assertStatus(403);
    }

    public function test_calendar_sync_updates_last_synced_at()
    {
        $calendar = ExternalCalendar::factory()->create([
            'property_id' => $this->property->id,
            'last_synced_at' => null,
        ]);

        $this->actingAs($this->owner, 'sanctum')
            ->postJson("/api/v1/external-calendars/{$calendar->id}/sync");

        $calendar->refresh();
        $this->assertNotNull($calendar->last_synced_at);
    }
}
