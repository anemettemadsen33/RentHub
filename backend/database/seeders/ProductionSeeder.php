<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Production Seeder - Seeds only essential data required for production
 * Run this on production deployment: php artisan db:seed --class=ProductionSeeder
 */
class ProductionSeeder extends Seeder
{
    /**
     * Run the production database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Seeding production data...');

        // 1. Roles & Permissions (MUST run first)
        $this->command->info('📝 Seeding roles and permissions...');
        $this->call(RolePermissionSeeder::class);

        // 2. Languages
        $this->command->info('🌐 Seeding languages...');
        $this->call(LanguageSeeder::class);

        // 3. Currencies & Exchange Rates
        $this->command->info('💰 Seeding currencies...');
        $this->call(CurrencySeeder::class);

        // 4. Admin User
        $this->command->info('👤 Creating admin user...');
        $this->call(AdminSeeder::class);

        // 5. Amenities
        $this->command->info('🏠 Seeding amenities...');
        $this->call(AmenitySeeder::class);

        // 6. Settings (if exists)
        if (class_exists(\Database\Seeders\SettingsSeeder::class)) {
            $this->command->info('⚙️  Seeding system settings...');
            $this->call(SettingsSeeder::class);
        }

        $this->command->newLine();
        $this->command->info('✅ Production seeding completed successfully!');
        $this->command->newLine();
        $this->command->warn('⚠️  IMPORTANT: Change admin password immediately!');
        $this->command->info('   Email: admin@renthub.com');
        $this->command->info('   Password: Admin@123456');
        $this->command->info('   URL: https://renthub-tbj7yxj7.on-forge.com/admin');
        $this->command->newLine();
    }
}
