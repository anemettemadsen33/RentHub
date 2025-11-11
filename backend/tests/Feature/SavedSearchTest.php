<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_user_can_save_search()
    {
        $searchData = [
            'name' => 'Beach Houses',
            'filters' => [
                'type' => 'house',
                'min_price' => 100,
                'max_price' => 500,
                'city' => 'Miami',
            ],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/saved-searches', $searchData);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'Beach Houses']);

        $this->assertDatabaseHas('saved_searches', [
            'user_id' => $this->user->id,
            'name' => 'Beach Houses',
        ]);
    }

    public function test_user_can_view_saved_searches()
    {
        SavedSearch::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/saved-searches');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_single_saved_search()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/saved-searches/{$savedSearch->id}");

        $response->assertOk()
            ->assertJsonFragment(['id' => $savedSearch->id]);
    }

    public function test_user_can_update_saved_search()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/saved-searches/{$savedSearch->id}", [
                'name' => 'Updated Search',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('saved_searches', [
            'id' => $savedSearch->id,
            'name' => 'Updated Search',
        ]);
    }

    public function test_user_can_delete_saved_search()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/saved-searches/{$savedSearch->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('saved_searches', ['id' => $savedSearch->id]);
    }

    public function test_user_can_execute_saved_search()
    {
        Property::factory()->create([
            'type' => 'house',
            'price' => 300,
            'city' => 'Miami',
            'status' => 'available',
        ]);

        Property::factory()->create([
            'type' => 'apartment',
            'price' => 150,
            'city' => 'New York',
            'status' => 'available',
        ]);

        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
            'filters' => [
                'type' => 'house',
                'city' => 'Miami',
            ],
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/saved-searches/{$savedSearch->id}/execute");

        $response->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_enable_notifications_for_saved_search()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
            'notify' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/saved-searches/{$savedSearch->id}", [
                'notify' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('saved_searches', [
            'id' => $savedSearch->id,
            'notify' => true,
        ]);
    }

    public function test_user_cannot_view_other_user_saved_search()
    {
        $otherUser = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/saved-searches/{$savedSearch->id}");

        $response->assertStatus(403);
    }

    public function test_saved_search_stores_filters_as_json()
    {
        $filters = [
            'type' => 'apartment',
            'min_price' => 100,
            'max_price' => 500,
            'bedrooms' => 2,
            'amenities' => ['wifi', 'pool'],
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/saved-searches', [
                'name' => 'Test Search',
                'filters' => $filters,
            ]);

        $response->assertCreated();

        $savedSearch = SavedSearch::where('user_id', $this->user->id)->first();
        $this->assertEquals($filters, $savedSearch->filters);
    }

    public function test_user_can_have_multiple_saved_searches()
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/saved-searches', [
                'name' => 'Search 1',
                'filters' => ['type' => 'house'],
            ]);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/saved-searches', [
                'name' => 'Search 2',
                'filters' => ['type' => 'apartment'],
            ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/saved-searches');

        $response->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_saved_search_requires_name()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/saved-searches', [
                'filters' => ['type' => 'house'],
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_saved_search_records_last_executed_time()
    {
        $savedSearch = SavedSearch::factory()->create([
            'user_id' => $this->user->id,
            'last_executed_at' => null,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/saved-searches/{$savedSearch->id}/execute");

        $savedSearch->refresh();
        $this->assertNotNull($savedSearch->last_executed_at);
    }
}
