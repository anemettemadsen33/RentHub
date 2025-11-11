<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create roles
        $tenant = Role::firstOrCreate(['name' => 'tenant', 'guard_name' => 'web']);
        $guest = Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']); // Alias for tenant
        $owner = Role::firstOrCreate(['name' => 'owner', 'guard_name' => 'web']);
        $host = Role::firstOrCreate(['name' => 'host', 'guard_name' => 'web']); // Alias for owner
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        // Create permissions
        $permissions = [
            // Property permissions
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',

            // Booking permissions
            'view bookings',
            'create bookings',
            'cancel bookings',
            'manage bookings',

            // User permissions
            'manage users',
            'view users',

            // Payment permissions
            'process payments',
            'refund payments',
            'view payments',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign permissions to roles
        // Tenant permissions (same as guest)
        $tenantPermissions = [
            'view properties',
            'view bookings',
            'create bookings',
            'cancel bookings',
            'view payments',
        ];
        $tenant->givePermissionTo($tenantPermissions);
        $guest->givePermissionTo($tenantPermissions);

        // Owner permissions (same as host)
        $ownerPermissions = [
            'view properties',
            'create properties',
            'edit properties',
            'delete properties',
            'view bookings',
            'manage bookings',
            'process payments',
            'view payments',
        ];
        $owner->givePermissionTo($ownerPermissions);
        $host->givePermissionTo($ownerPermissions);

        $admin->givePermissionTo(Permission::all());

        $this->command->info('✅ Roles and permissions created successfully!');
        $this->command->info('   - tenant/guest: Basic user permissions');
        $this->command->info('   - owner/host: Property management permissions');
        $this->command->info('   - admin: All permissions');
    }
}
