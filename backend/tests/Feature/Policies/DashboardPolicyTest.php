<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class DashboardPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_access_owner_dashboard()
    {
        $owner = User::factory()->create(['role' => 'owner']);

        $this->assertTrue(Gate::forUser($owner)->allows('view-owner-dashboard'));
    }

    public function test_tenant_cannot_access_owner_dashboard()
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->assertFalse(Gate::forUser($tenant)->allows('view-owner-dashboard'));
    }

    public function test_admin_can_access_all_dashboards()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue(Gate::forUser($admin)->allows('view-owner-dashboard'));
        $this->assertTrue(Gate::forUser($admin)->allows('view-tenant-dashboard'));
        $this->assertTrue(Gate::forUser($admin)->allows('view-admin-dashboard'));
    }

    public function test_tenant_can_access_tenant_dashboard()
    {
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->assertTrue(Gate::forUser($tenant)->allows('view-tenant-dashboard'));
    }

    public function test_guest_can_access_tenant_dashboard()
    {
        $guest = User::factory()->create(['role' => 'guest']);

        $this->assertTrue(Gate::forUser($guest)->allows('view-tenant-dashboard'));
    }

    public function test_only_admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->assertTrue(Gate::forUser($admin)->allows('view-admin-dashboard'));
        $this->assertFalse(Gate::forUser($owner)->allows('view-admin-dashboard'));
        $this->assertFalse(Gate::forUser($tenant)->allows('view-admin-dashboard'));
    }

    public function test_owner_and_admin_can_view_analytics()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $owner = User::factory()->create(['role' => 'owner']);
        $tenant = User::factory()->create(['role' => 'tenant']);

        $this->assertTrue(Gate::forUser($admin)->allows('view-analytics'));
        $this->assertTrue(Gate::forUser($owner)->allows('view-analytics'));
        $this->assertFalse(Gate::forUser($tenant)->allows('view-analytics'));
    }
}
