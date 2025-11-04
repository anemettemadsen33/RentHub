<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RBACService
{
    /**
     * Permission cache TTL (1 hour)
     */
    protected int $cacheTtl = 3600;

    /**
     * Check if user has permission
     */
    public function hasPermission(User $user, string $permission): bool
    {
        return $this->getUserPermissions($user)->contains($permission);
    }

    /**
     * Check if user has any of the permissions
     */
    public function hasAnyPermission(User $user, array $permissions): bool
    {
        $userPermissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if ($userPermissions->contains($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if user has all permissions
     */
    public function hasAllPermissions(User $user, array $permissions): bool
    {
        $userPermissions = $this->getUserPermissions($user);
        
        foreach ($permissions as $permission) {
            if (!$userPermissions->contains($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get all user permissions (with caching)
     */
    public function getUserPermissions(User $user): Collection
    {
        return Cache::remember(
            "user:{$user->id}:permissions",
            $this->cacheTtl,
            fn() => $this->loadUserPermissions($user)
        );
    }

    /**
     * Load user permissions from database
     */
    protected function loadUserPermissions(User $user): Collection
    {
        // Get direct permissions
        $directPermissions = $user->permissions->pluck('name');
        
        // Get role permissions
        $rolePermissions = $user->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');
        
        return $directPermissions->merge($rolePermissions)->unique();
    }

    /**
     * Assign role to user
     */
    public function assignRole(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        
        if (!$user->roles->contains($role->id)) {
            $user->roles()->attach($role->id);
            $this->clearUserCache($user);
            return true;
        }
        
        return false;
    }

    /**
     * Remove role from user
     */
    public function removeRole(User $user, string $roleName): bool
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        
        if ($user->roles->contains($role->id)) {
            $user->roles()->detach($role->id);
            $this->clearUserCache($user);
            return true;
        }
        
        return false;
    }

    /**
     * Sync user roles
     */
    public function syncRoles(User $user, array $roleNames): void
    {
        $roles = Role::whereIn('name', $roleNames)->pluck('id');
        $user->roles()->sync($roles);
        $this->clearUserCache($user);
    }

    /**
     * Grant permission to user
     */
    public function grantPermission(User $user, string $permissionName): bool
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        
        if (!$user->permissions->contains($permission->id)) {
            $user->permissions()->attach($permission->id);
            $this->clearUserCache($user);
            return true;
        }
        
        return false;
    }

    /**
     * Revoke permission from user
     */
    public function revokePermission(User $user, string $permissionName): bool
    {
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        
        if ($user->permissions->contains($permission->id)) {
            $user->permissions()->detach($permission->id);
            $this->clearUserCache($user);
            return true;
        }
        
        return false;
    }

    /**
     * Create new role
     */
    public function createRole(string $name, string $description, array $permissionNames = []): Role
    {
        $role = Role::create([
            'name' => $name,
            'description' => $description,
        ]);
        
        if (!empty($permissionNames)) {
            $permissions = Permission::whereIn('name', $permissionNames)->pluck('id');
            $role->permissions()->sync($permissions);
        }
        
        return $role;
    }

    /**
     * Create new permission
     */
    public function createPermission(string $name, string $description, ?string $group = null): Permission
    {
        return Permission::create([
            'name' => $name,
            'description' => $description,
            'group' => $group,
        ]);
    }

    /**
     * Assign permission to role
     */
    public function assignPermissionToRole(string $roleName, string $permissionName): bool
    {
        $role = Role::where('name', $roleName)->firstOrFail();
        $permission = Permission::where('name', $permissionName)->firstOrFail();
        
        if (!$role->permissions->contains($permission->id)) {
            $role->permissions()->attach($permission->id);
            $this->clearRoleCache($role);
            return true;
        }
        
        return false;
    }

    /**
     * Check if user has role
     */
    public function hasRole(User $user, string $roleName): bool
    {
        return $user->roles->contains('name', $roleName);
    }

    /**
     * Check if user has any of the roles
     */
    public function hasAnyRole(User $user, array $roleNames): bool
    {
        foreach ($roleNames as $roleName) {
            if ($this->hasRole($user, $roleName)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Get user roles
     */
    public function getUserRoles(User $user): Collection
    {
        return $user->roles->pluck('name');
    }

    /**
     * Clear user permission cache
     */
    public function clearUserCache(User $user): void
    {
        Cache::forget("user:{$user->id}:permissions");
    }

    /**
     * Clear role cache
     */
    public function clearRoleCache(Role $role): void
    {
        // Clear cache for all users with this role
        $role->users->each(fn($user) => $this->clearUserCache($user));
    }

    /**
     * Clear all permission caches
     */
    public function clearAllCaches(): void
    {
        Cache::flush();
    }

    /**
     * Seed default roles and permissions
     */
    public function seedDefaults(): void
    {
        // Create permissions
        $permissions = [
            // Property permissions
            ['name' => 'properties.view', 'description' => 'View properties', 'group' => 'properties'],
            ['name' => 'properties.create', 'description' => 'Create properties', 'group' => 'properties'],
            ['name' => 'properties.update', 'description' => 'Update properties', 'group' => 'properties'],
            ['name' => 'properties.delete', 'description' => 'Delete properties', 'group' => 'properties'],
            
            // Booking permissions
            ['name' => 'bookings.view', 'description' => 'View bookings', 'group' => 'bookings'],
            ['name' => 'bookings.create', 'description' => 'Create bookings', 'group' => 'bookings'],
            ['name' => 'bookings.update', 'description' => 'Update bookings', 'group' => 'bookings'],
            ['name' => 'bookings.cancel', 'description' => 'Cancel bookings', 'group' => 'bookings'],
            
            // User permissions
            ['name' => 'users.view', 'description' => 'View users', 'group' => 'users'],
            ['name' => 'users.create', 'description' => 'Create users', 'group' => 'users'],
            ['name' => 'users.update', 'description' => 'Update users', 'group' => 'users'],
            ['name' => 'users.delete', 'description' => 'Delete users', 'group' => 'users'],
            
            // Payment permissions
            ['name' => 'payments.view', 'description' => 'View payments', 'group' => 'payments'],
            ['name' => 'payments.process', 'description' => 'Process payments', 'group' => 'payments'],
            ['name' => 'payments.refund', 'description' => 'Refund payments', 'group' => 'payments'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        // Create roles
        $admin = $this->createRole('admin', 'Administrator with full access');
        $landlord = $this->createRole('landlord', 'Property owner');
        $tenant = $this->createRole('tenant', 'Property renter');
        $guest = $this->createRole('guest', 'Basic user');

        // Assign permissions to roles
        $admin->permissions()->sync(Permission::all());
        
        $landlord->permissions()->sync(
            Permission::whereIn('name', [
                'properties.view', 'properties.create', 'properties.update', 'properties.delete',
                'bookings.view', 'bookings.update', 'bookings.cancel',
                'payments.view',
            ])->pluck('id')
        );
        
        $tenant->permissions()->sync(
            Permission::whereIn('name', [
                'properties.view',
                'bookings.view', 'bookings.create', 'bookings.cancel',
                'payments.view',
            ])->pluck('id')
        );
        
        $guest->permissions()->sync(
            Permission::whereIn('name', [
                'properties.view',
            ])->pluck('id')
        );
    }
}
