<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestPropertiesSeeder extends Seeder
{
    /**
     * Seed test properties for development and testing.
     */
    public function run(): void
    {
        // Create test owner user if not exists
        $owner = User::firstOrCreate(
            ['email' => 'owner@renthub.test'],
            [
                'name' => 'Test Property Owner',
                'password' => bcrypt('password123'),
                'email_verified_at' => now(),
            ]
        );

        // Test Properties
        $properties = [
            [
                'user_id' => $owner->id,
                'type' => 'apartment',
                'title' => 'Luxury Downtown Apartment',
                'description' => 'Beautiful 2-bedroom apartment in the heart of downtown. Features modern amenities, stunning city views, and close proximity to restaurants and shopping.',
                'street_address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10001',
                'latitude' => 40.7505,
                'longitude' => -73.9934,
                'price_per_night' => 250.00,
                'price_per_week' => 1500.00,
                'price_per_month' => 2500.00,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqm' => 111,
                'square_footage' => 1200,
                'guests' => 4,
                'min_nights' => 2,
                'max_nights' => 30,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $owner->id,
                'type' => 'house',
                'title' => 'Cozy Suburban Family Home',
                'description' => 'Spacious 3-bedroom house in quiet neighborhood. Perfect for families with large backyard, modern kitchen, and excellent schools nearby.',
                'street_address' => '456 Oak Avenue',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'country' => 'USA',
                'postal_code' => '90001',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'price_per_night' => 320.00,
                'price_per_week' => 2000.00,
                'price_per_month' => 3200.00,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqm' => 186,
                'square_footage' => 2000,
                'guests' => 6,
                'min_nights' => 3,
                'max_nights' => 60,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $owner->id,
                'type' => 'villa',
                'title' => 'Beachfront Luxury Villa',
                'description' => 'Stunning 5-bedroom villa right on the beach. Features private pool, ocean views from every room, and world-class amenities. Perfect for luxury vacation.',
                'street_address' => '789 Beach Boulevard',
                'city' => 'Miami',
                'state' => 'FL',
                'country' => 'USA',
                'postal_code' => '33101',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'price_per_night' => 850.00,
                'price_per_week' => 5500.00,
                'price_per_month' => 8500.00,
                'bedrooms' => 5,
                'bathrooms' => 4,
                'area_sqm' => 418,
                'square_footage' => 4500,
                'guests' => 10,
                'min_nights' => 5,
                'max_nights' => 90,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $owner->id,
                'type' => 'studio',
                'title' => 'Modern Downtown Studio',
                'description' => 'Compact and efficient studio in prime location. Perfect for solo travelers or business professionals. Walking distance to metro and business district.',
                'street_address' => '321 Broadway',
                'city' => 'Chicago',
                'state' => 'IL',
                'country' => 'USA',
                'postal_code' => '60601',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'price_per_night' => 150.00,
                'price_per_week' => 900.00,
                'price_per_month' => 1500.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqm' => 46,
                'square_footage' => 500,
                'guests' => 2,
                'min_nights' => 1,
                'max_nights' => 30,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $owner->id,
                'type' => 'apartment',
                'title' => 'Penthouse with Skyline Views',
                'description' => 'Exclusive penthouse on 40th floor. Panoramic city views, high-end finishes, private elevator access. The ultimate luxury living experience.',
                'street_address' => '555 Park Avenue',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'USA',
                'postal_code' => '10022',
                'latitude' => 40.7614,
                'longitude' => -73.9776,
                'price_per_night' => 1200.00,
                'price_per_week' => 7500.00,
                'price_per_month' => 12000.00,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqm' => 325,
                'square_footage' => 3500,
                'guests' => 8,
                'min_nights' => 7,
                'max_nights' => 180,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::firstOrCreate(
                [
                    'title' => $propertyData['title'],
                    'user_id' => $propertyData['user_id']
                ],
                $propertyData
            );
        }

        $this->command->info('✅ Created ' . count($properties) . ' test properties');
        $this->command->info('📧 Test owner email: owner@renthub.test');
        $this->command->info('🔑 Test owner password: password123');
    }
}
