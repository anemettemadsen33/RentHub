<?php

namespace Tests\Feature\Policies;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SavedSearchPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_saved_search()
    {
        $user = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('view', $savedSearch));
    }

    public function test_user_cannot_view_other_users_saved_search()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('view', $savedSearch));
    }

    public function test_user_can_update_own_saved_search()
    {
        $user = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('update', $savedSearch));
    }

    public function test_user_cannot_update_other_users_saved_search()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('update', $savedSearch));
    }

    public function test_user_can_delete_own_saved_search()
    {
        $user = User::factory()->create();
        $savedSearch = SavedSearch::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('delete', $savedSearch));
    }
}
