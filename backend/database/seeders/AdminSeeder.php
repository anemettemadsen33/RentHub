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
            $this->command->info('⚠️  Admin user already exists!');
            $this->command->info('📧 Email: admin@renthub.com');
            $this->command->info('🔑 Password: Admin@123456');

            return;
        }

        // Dynamically build attributes only for existing columns to avoid errors on slim test schemas
        $base = [
            'name' => 'Admin RentHub',
            'email' => 'admin@renthub.com',
            'password' => Hash::make('Admin@123456'),
            'role' => 'admin',
            'is_admin' => true, // For Filament admin panel access
        ];

        $optional = [
            'phone' => '+1234567890',
            'email_verified_at' => now(),
            'bio' => 'System Administrator - Full Access',
            'country' => 'US',
            'language' => 'en',
            'currency' => 'USD',
            'timezone' => 'UTC',
        ];

        foreach ($optional as $column => $value) {
            if (\Schema::hasColumn('users', $column)) {
                $base[$column] = $value;
            }
        }

        $admin = User::create($base);

        $this->command->info('✅ Admin user created successfully!');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('📧 Email:    admin@renthub.com');
        $this->command->info('🔑 Password: Admin@123456');
        $this->command->info('🎯 Role:     Administrator (Full Access)');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🌐 Access Filament Admin:');
        $this->command->info('   https://renthub-tbj7yxj7.on-forge.com/admin');
        $this->command->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->command->info('🔑 Password: Admin@123456');
        $this->command->warn('⚠️  IMPORTANT: Change the password after first login!');
    }
}
