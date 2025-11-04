<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RBACSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Roles
        $superAdmin = Role::create([
            'name' => 'super_admin',
            'description' => 'Full system access',
        ]);

        $propertyManager = Role::create([
            'name' => 'property_manager',
            'description' => 'Property management access',
        ]);

        $owner = Role::create([
            'name' => 'owner',
            'description' => 'Property owner access',
        ]);

        $guest = Role::create([
            'name' => 'guest',
            'description' => 'Guest user access',
        ]);

        // Create Permissions
        $permissions = [
            // Property permissions
            ['name' => 'properties.view', 'description' => 'View properties', 'category' => 'properties'],
            ['name' => 'properties.create', 'description' => 'Create properties', 'category' => 'properties'],
            ['name' => 'properties.edit', 'description' => 'Edit properties', 'category' => 'properties'],
            ['name' => 'properties.delete', 'description' => 'Delete properties', 'category' => 'properties'],

            // Booking permissions
            ['name' => 'bookings.view', 'description' => 'View bookings', 'category' => 'bookings'],
            ['name' => 'bookings.create', 'description' => 'Create bookings', 'category' => 'bookings'],
            ['name' => 'bookings.edit', 'description' => 'Edit bookings', 'category' => 'bookings'],
            ['name' => 'bookings.cancel', 'description' => 'Cancel bookings', 'category' => 'bookings'],

            // User permissions
            ['name' => 'users.view', 'description' => 'View users', 'category' => 'users'],
            ['name' => 'users.create', 'description' => 'Create users', 'category' => 'users'],
            ['name' => 'users.edit', 'description' => 'Edit users', 'category' => 'users'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'category' => 'users'],

            // Review permissions
            ['name' => 'reviews.view', 'description' => 'View reviews', 'category' => 'reviews'],
            ['name' => 'reviews.create', 'description' => 'Create reviews', 'category' => 'reviews'],
            ['name' => 'reviews.edit', 'description' => 'Edit own reviews', 'category' => 'reviews'],
            ['name' => 'reviews.delete', 'description' => 'Delete reviews', 'category' => 'reviews'],

            // Payment permissions
            ['name' => 'payments.view', 'description' => 'View payments', 'category' => 'payments'],
            ['name' => 'payments.process', 'description' => 'Process payments', 'category' => 'payments'],
            ['name' => 'payments.refund', 'description' => 'Refund payments', 'category' => 'payments'],

            // Analytics permissions
            ['name' => 'analytics.view', 'description' => 'View analytics', 'category' => 'analytics'],
            ['name' => 'analytics.export', 'description' => 'Export analytics', 'category' => 'analytics'],

            // Settings permissions
            ['name' => 'settings.view', 'description' => 'View settings', 'category' => 'settings'],
            ['name' => 'settings.edit', 'description' => 'Edit settings', 'category' => 'settings'],
        ];

        foreach ($permissions as $permissionData) {
            Permission::create($permissionData);
        }

        // Assign permissions to roles
        $allPermissions = Permission::all();

        // Super Admin - All permissions
        $superAdmin->permissions()->attach($allPermissions->pluck('id'));

        // Property Manager - Property, booking, review management
        $propertyManagerPermissions = Permission::whereIn('category', [
            'properties', 'bookings', 'reviews', 'analytics',
        ])->pluck('id');
        $propertyManager->permissions()->attach($propertyManagerPermissions);

        // Owner - View and manage own properties
        $ownerPermissions = Permission::whereIn('name', [
            'properties.view',
            'properties.create',
            'properties.edit',
            'bookings.view',
            'reviews.view',
            'analytics.view',
        ])->pluck('id');
        $owner->permissions()->attach($ownerPermissions);

        // Guest - Basic permissions
        $guestPermissions = Permission::whereIn('name', [
            'properties.view',
            'bookings.create',
            'bookings.view',
            'reviews.create',
            'reviews.view',
            'reviews.edit',
        ])->pluck('id');
        $guest->permissions()->attach($guestPermissions);

        $this->command->info('RBAC structure seeded successfully!');
    }
}
