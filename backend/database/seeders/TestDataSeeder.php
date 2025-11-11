<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Creating test data...');

        // Create landlord user (owner role)
        $landlord = User::firstOrCreate(
            ['email' => 'landlord@renthub.test'],
            [
                'name' => 'Test Landlord',
                'password' => bcrypt('landlord123'),
                'role' => 'owner', // Changed from 'landlord' to 'owner'
                'email_verified_at' => now(),
            ]
        );
        $this->command->info('✓ Landlord user created');

        // Create amenities
        $amenities = [
            ['name' => 'WiFi', 'icon' => 'wifi', 'category' => 'internet'],
            ['name' => 'Air Conditioning', 'icon' => 'snowflake', 'category' => 'comfort'],
            ['name' => 'Parking', 'icon' => 'car', 'category' => 'parking'],
            ['name' => 'Kitchen', 'icon' => 'utensils', 'category' => 'kitchen'],
            ['name' => 'TV', 'icon' => 'tv', 'category' => 'entertainment'],
            ['name' => 'Washer', 'icon' => 'washing-machine', 'category' => 'laundry'],
            ['name' => 'Gym', 'icon' => 'dumbbell', 'category' => 'fitness'],
            ['name' => 'Pool', 'icon' => 'swimming-pool', 'category' => 'recreation'],
        ];

        foreach ($amenities as $amenityData) {
            Amenity::firstOrCreate(
                ['name' => $amenityData['name']],
                $amenityData
            );
        }
        $this->command->info('✓ Amenities created');

        // Create test properties
        $properties = [
            [
                'title' => 'Modern Downtown Apartment',
                'description' => 'Beautiful modern apartment in the heart of the city with stunning views and all amenities.',
                'type' => 'apartment',
                'status' => 'available',
                'price_per_night' => 150.00,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'guests' => 4,
                'area_sqm' => 85.5,
                'street_address' => '123 Main Street',
                'city' => 'New York',
                'state' => 'NY',
                'country' => 'US',
                'postal_code' => '10001',
                'latitude' => 40.7580,
                'longitude' => -73.9855,
            ],
            [
                'title' => 'Cozy Studio Near University',
                'description' => 'Perfect for students! Close to campus with excellent public transport connections.',
                'type' => 'studio',
                'status' => 'available',
                'price_per_night' => 75.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'guests' => 2,
                'area_sqm' => 35.0,
                'street_address' => '456 College Ave',
                'city' => 'Boston',
                'state' => 'MA',
                'country' => 'US',
                'postal_code' => '02115',
                'latitude' => 42.3505,
                'longitude' => -71.1054,
            ],
            [
                'title' => 'Luxury Penthouse with Ocean View',
                'description' => 'Spectacular penthouse with panoramic ocean views, private terrace, and premium finishes.',
                'type' => 'penthouse',
                'status' => 'available',
                'price_per_night' => 500.00,
                'bedrooms' => 3,
                'bathrooms' => 3,
                'guests' => 6,
                'area_sqm' => 200.0,
                'street_address' => '789 Beach Boulevard',
                'city' => 'Miami',
                'state' => 'FL',
                'country' => 'US',
                'postal_code' => '33139',
                'latitude' => 25.7907,
                'longitude' => -80.1300,
            ],
            [
                'title' => 'Family House in Suburbs',
                'description' => 'Spacious family home with garden, perfect for families with children. Quiet neighborhood.',
                'type' => 'house',
                'status' => 'available',
                'price_per_night' => 200.00,
                'bedrooms' => 4,
                'bathrooms' => 2,
                'guests' => 8,
                'area_sqm' => 180.0,
                'street_address' => '321 Oak Street',
                'city' => 'Austin',
                'state' => 'TX',
                'country' => 'US',
                'postal_code' => '78701',
                'latitude' => 30.2747,
                'longitude' => -97.7404,
            ],
        ];

        foreach ($properties as $propertyData) {
            $property = Property::firstOrCreate(
                ['title' => $propertyData['title']],
                array_merge($propertyData, ['user_id' => $landlord->id])
            );

            // Attach random amenities
            $randomAmenities = Amenity::inRandomOrder()->limit(rand(3, 6))->pluck('id');
            $property->amenities()->syncWithoutDetaching($randomAmenities);
        }

        $this->command->info('✓ Test properties created');
        $this->command->info('');
        $this->command->info('Test data seeding completed!');
        $this->command->info('Landlord: landlord@renthub.test / landlord123');
        $this->command->info('Properties created: ' . count($properties));
    }
}
