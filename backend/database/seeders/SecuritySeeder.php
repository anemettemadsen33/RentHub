<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SecuritySeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $this->seedPermissions();
            $this->seedRoles();
            $this->assignPermissionsToRoles();
        });
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // Property Management
            ['name' => 'property.view', 'description' => 'View properties', 'category' => 'property'],
            ['name' => 'property.create', 'description' => 'Create properties', 'category' => 'property'],
            ['name' => 'property.edit', 'description' => 'Edit properties', 'category' => 'property'],
            ['name' => 'property.delete', 'description' => 'Delete properties', 'category' => 'property'],
            ['name' => 'property.publish', 'description' => 'Publish properties', 'category' => 'property'],

            // Booking Management
            ['name' => 'booking.view', 'description' => 'View bookings', 'category' => 'booking'],
            ['name' => 'booking.create', 'description' => 'Create bookings', 'category' => 'booking'],
            ['name' => 'booking.edit', 'description' => 'Edit bookings', 'category' => 'booking'],
            ['name' => 'booking.cancel', 'description' => 'Cancel bookings', 'category' => 'booking'],
            ['name' => 'booking.approve', 'description' => 'Approve bookings', 'category' => 'booking'],

            // User Management
            ['name' => 'user.view', 'description' => 'View users', 'category' => 'user'],
            ['name' => 'user.create', 'description' => 'Create users', 'category' => 'user'],
            ['name' => 'user.edit', 'description' => 'Edit users', 'category' => 'user'],
            ['name' => 'user.delete', 'description' => 'Delete users', 'category' => 'user'],
            ['name' => 'user.impersonate', 'description' => 'Impersonate users', 'category' => 'user'],

            // Payment Management
            ['name' => 'payment.view', 'description' => 'View payments', 'category' => 'payment'],
            ['name' => 'payment.process', 'description' => 'Process payments', 'category' => 'payment'],
            ['name' => 'payment.refund', 'description' => 'Refund payments', 'category' => 'payment'],

            // Review Management
            ['name' => 'review.view', 'description' => 'View reviews', 'category' => 'review'],
            ['name' => 'review.create', 'description' => 'Create reviews', 'category' => 'review'],
            ['name' => 'review.edit', 'description' => 'Edit reviews', 'category' => 'review'],
            ['name' => 'review.delete', 'description' => 'Delete reviews', 'category' => 'review'],
            ['name' => 'review.moderate', 'description' => 'Moderate reviews', 'category' => 'review'],

            // Analytics
            ['name' => 'analytics.view', 'description' => 'View analytics', 'category' => 'analytics'],
            ['name' => 'analytics.export', 'description' => 'Export analytics', 'category' => 'analytics'],

            // Settings
            ['name' => 'settings.view', 'description' => 'View settings', 'category' => 'settings'],
            ['name' => 'settings.edit', 'description' => 'Edit settings', 'category' => 'settings'],

            // Security
            ['name' => 'security.audit', 'description' => 'View security audits', 'category' => 'security'],
            ['name' => 'security.scan', 'description' => 'Run security scans', 'category' => 'security'],
            ['name' => 'security.incidents', 'description' => 'Manage security incidents', 'category' => 'security'],

            // API
            ['name' => 'api.access', 'description' => 'Access API', 'category' => 'api'],
            ['name' => 'api.keys', 'description' => 'Manage API keys', 'category' => 'api'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }
    }

    private function seedRoles(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Full system access with all permissions',
            ],
            [
                'name' => 'landlord',
                'description' => 'Property owners who can manage their properties and bookings',
            ],
            [
                'name' => 'tenant',
                'description' => 'Regular users who can book properties and leave reviews',
            ],
            [
                'name' => 'property_manager',
                'description' => 'Manage properties on behalf of landlords',
            ],
            [
                'name' => 'guest',
                'description' => 'Limited access for browsing only',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                ['name' => $role['name']],
                $role
            );
        }
    }

    private function assignPermissionsToRoles(): void
    {
        // Admin - All permissions
        $admin = Role::where('name', 'admin')->first();
        $admin->permissions()->sync(Permission::all()->pluck('id'));

        // Landlord permissions
        $landlord = Role::where('name', 'landlord')->first();
        $landlordPermissions = Permission::whereIn('name', [
            'property.view', 'property.create', 'property.edit', 'property.delete', 'property.publish',
            'booking.view', 'booking.approve', 'booking.cancel',
            'payment.view',
            'review.view', 'review.delete',
            'analytics.view', 'analytics.export',
            'api.access', 'api.keys',
        ])->pluck('id');
        $landlord->permissions()->sync($landlordPermissions);

        // Tenant permissions
        $tenant = Role::where('name', 'tenant')->first();
        $tenantPermissions = Permission::whereIn('name', [
            'property.view',
            'booking.view', 'booking.create', 'booking.cancel',
            'payment.view',
            'review.view', 'review.create', 'review.edit',
            'api.access',
        ])->pluck('id');
        $tenant->permissions()->sync($tenantPermissions);

        // Property Manager permissions
        $propertyManager = Role::where('name', 'property_manager')->first();
        $propertyManagerPermissions = Permission::whereIn('name', [
            'property.view', 'property.edit', 'property.publish',
            'booking.view', 'booking.approve', 'booking.cancel',
            'payment.view',
            'review.view', 'review.moderate',
            'analytics.view',
            'api.access',
        ])->pluck('id');
        $propertyManager->permissions()->sync($propertyManagerPermissions);

        // Guest permissions
        $guest = Role::where('name', 'guest')->first();
        $guestPermissions = Permission::whereIn('name', [
            'property.view',
            'review.view',
        ])->pluck('id');
        $guest->permissions()->sync($guestPermissions);
    }
}
