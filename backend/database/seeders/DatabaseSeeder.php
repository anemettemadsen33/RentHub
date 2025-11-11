<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed essential data first
        $this->call([
            RolePermissionSeeder::class, // Must be first!
            LanguageSeeder::class,
            CurrencySeeder::class,
            AdminSeeder::class,
        ]);

        // Uncomment to create test users
        // User::factory(10)->create();

        // Uncomment to create test user
        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
