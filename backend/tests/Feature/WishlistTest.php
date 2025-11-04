<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WishlistTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->property = Property::factory()->create(['status' => 'published']);
    }

    public function test_user_can_create_wishlist()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/v1/wishlists', [
                'name' => 'My Favorites',
                'description' => 'Properties I love',
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['name' => 'My Favorites']);

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $this->user->id,
            'name' => 'My Favorites',
        ]);
    }

    public function test_user_can_add_property_to_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/wishlists/{$wishlist->id}/items", [
                'property_id' => $this->property->id,
            ]);

        $response->assertCreated();

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_id' => $wishlist->id,
            'property_id' => $this->property->id,
        ]);
    }

    public function test_user_can_remove_property_from_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);
        $item = WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/wishlists/{$wishlist->id}/items/{$item->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('wishlist_items', ['id' => $item->id]);
    }

    public function test_user_can_view_their_wishlists()
    {
        Wishlist::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/v1/wishlists');

        $response->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_view_wishlist_with_properties()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);
        WishlistItem::factory()->count(3)->create([
            'wishlist_id' => $wishlist->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/wishlists/{$wishlist->id}");

        $response->assertOk()
            ->assertJsonCount(3, 'items');
    }

    public function test_user_can_update_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/wishlists/{$wishlist->id}", [
                'name' => 'Updated Name',
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_user_can_delete_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/v1/wishlists/{$wishlist->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('wishlists', ['id' => $wishlist->id]);
    }

    public function test_user_cannot_add_duplicate_property_to_wishlist()
    {
        $wishlist = Wishlist::factory()->create(['user_id' => $this->user->id]);
        WishlistItem::factory()->create([
            'wishlist_id' => $wishlist->id,
            'property_id' => $this->property->id,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson("/api/v1/wishlists/{$wishlist->id}/items", [
                'property_id' => $this->property->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_user_cannot_access_other_user_wishlist()
    {
        $otherUser = User::factory()->create();
        $wishlist = Wishlist::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/v1/wishlists/{$wishlist->id}");

        $response->assertStatus(403);
    }

    public function test_default_wishlist_created_on_user_registration()
    {
        $newUser = User::factory()->create();

        $this->assertDatabaseHas('wishlists', [
            'user_id' => $newUser->id,
            'name' => 'Favorites',
            'is_default' => true,
        ]);
    }

    public function test_user_can_make_wishlist_public()
    {
        $wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/v1/wishlists/{$wishlist->id}", [
                'is_public' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('wishlists', [
            'id' => $wishlist->id,
            'is_public' => true,
        ]);
    }

    public function test_guest_can_view_public_wishlist()
    {
        $wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => true,
        ]);

        $response = $this->getJson("/api/v1/wishlists/{$wishlist->id}/public");

        $response->assertOk();
    }

    public function test_guest_cannot_view_private_wishlist()
    {
        $wishlist = Wishlist::factory()->create([
            'user_id' => $this->user->id,
            'is_public' => false,
        ]);

        $response = $this->getJson("/api/v1/wishlists/{$wishlist->id}/public");

        $response->assertStatus(403);
    }
}
