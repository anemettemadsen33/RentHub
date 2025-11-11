<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PropertyAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function test_host_role_has_permission()
    {
        // Check if role exists
        $role = \Spatie\Permission\Models\Role::where('name', 'host')->first();
        $this->assertNotNull($role, 'Host role should exist after seeding');
        $this->assertEquals('web', $role->guard_name, 'Host role should use web guard');
        
        $user = User::factory()->create();
        
        // Try to assign role
        $user->assignRole('host');
        
        // Force fresh from DB
        $user->refresh();
        $user->load('roles');
        
        dump('User ID: ' . $user->id);
        dump('User roles count: ' . $user->roles->count());
        dump('User roles: ' . $user->roles->pluck('name')->join(', '));
        dump('Has role host: ' . ($user->hasRole('host') ? 'YES' : 'NO'));
        
        $this->assertTrue($user->hasRole('host'), 'User should have host role');
        $this->assertTrue($user->hasAnyRole(['host']), 'User should pass hasAnyRole for host');
        $this->assertTrue($user->hasAnyRole(['owner', 'host', 'admin']), 'User should pass hasAnyRole for owner/host/admin');
        
        $this->actingAs($user, 'sanctum');
        
        $this->assertAuthenticated('sanctum');
        $this->assertTrue(auth()->user()->hasRole('host'), 'Authenticated user should have host role');
    }
}
