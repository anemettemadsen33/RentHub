<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin role if not exists
        if (! Role::where('name', 'admin')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'admin', 'guard_name' => 'web']);
        }

        if (! Role::where('name', 'user')->where('guard_name', 'web')->exists()) {
            Role::create(['name' => 'user', 'guard_name' => 'web']);
        }

        // Example permission (optional)
        if (! Permission::where('name', 'view queues')->where('guard_name', 'web')->exists()) {
            Permission::create(['name' => 'view queues', 'guard_name' => 'web']);
        }
    }
}
