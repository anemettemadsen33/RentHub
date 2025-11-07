<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin already exists
        $adminExists = User::where('email', 'admin@renthub.com')->exists();

        if ($adminExists) {
            $this->command->info('Admin user already exists!');
            return;
        }

        // Create admin user
        $admin = User::create([
            'name' => 'Admin RentHub',
            'email' => 'admin@renthub.com',
            'password' => Hash::make('Admin@123456'), // Change this password!
            'role' => 'admin',
            'phone' => '+1234567890',
            'email_verified_at' => now(),
            'bio' => 'System Administrator',
            'country' => 'US',
        ]);

        $this->command->info('✅ Admin user created successfully!');
        $this->command->info('📧 Email: admin@renthub.com');
        $this->command->info('🔑 Password: Admin@123456');
        $this->command->warn('⚠️  IMPORTANT: Change the password after first login!');
    }
}
