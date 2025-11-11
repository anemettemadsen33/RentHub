<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Check if admin already exists
        $existing = User::where('email', 'admin@renthub.com')->first();
        
        if ($existing) {
            $this->command->info('Admin user already exists!');
            $this->command->info('Email: ' . $existing->email);
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin RentHub',
            'email' => 'admin@renthub.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->command->info('✓ Admin user created successfully!');
        $this->command->info('Email: admin@renthub.com');
        $this->command->info('Password: admin123');
        $this->command->info('ID: ' . $admin->id);
    }
}
