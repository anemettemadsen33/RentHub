<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Booking Test Data Seeder
 * Creates properties specifically optimized for booking flow testing
 * Run: php artisan db:seed --class=BookingTestSeeder
 */
class BookingTestSeeder extends Seeder
{
    /**
     * Run the booking test data seeds.
     */
    public function run(): void
    {
        $this->command->info('🧪 Creating booking test properties...');

        // Create test owner user
        $owner = User::firstOrCreate(
            ['email' => 'owner@renthub.test'],
            [
                'name' => 'Test Property Owner',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // Assign user role if exists
        if (method_exists($owner, 'assignRole') && !$owner->hasRole('admin')) {
            try {
                $owner->assignRole('user');
            } catch (\Exception $e) {
                // Role doesn't exist, skip
            }
        }

        // Create test guest user for bookings
        $guest = User::firstOrCreate(
            ['email' => 'guest@renthub.test'],
            [
                'name' => 'Test Guest User',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        if (method_exists($guest, 'assignRole') && !$guest->hasRole('admin')) {
            try {
                $guest->assignRole('user');
            } catch (\Exception $e) {
                // Role doesn't exist, skip
            }
        }

        // Get amenities for properties
        $basicAmenities = Amenity::whereIn('category', ['basic', 'comfort'])->pluck('id')->toArray();
        
        // Test Property 1: Budget Apartment (Available, Short-term)
        $property1 = Property::updateOrCreate(
            [
                'title' => 'Budget Test Apartment - Quick Booking',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'apartment',
                'description' => 'Perfect for booking tests: available year-round, instant booking enabled, low minimum nights. Ideal for automated testing and manual QA.',
                'street_address' => '100 Test Street',
                'city' => 'Bucharest',
                'state' => 'Bucharest',
                'country' => 'Romania',
                'postal_code' => '010101',
                'latitude' => 44.4268,
                'longitude' => 26.1025,
                'price_per_night' => 50.00,
                'price_per_week' => 300.00,
                'price_per_month' => 1000.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqm' => 45,
                'square_footage' => 484,
                'guests' => 2,
                'min_nights' => 1,
                'max_nights' => 30,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
            ]
        );
        if (!empty($basicAmenities)) {
            $property1->amenities()->sync(array_slice($basicAmenities, 0, 3));
        }

        // Test Property 2: Mid-range House (Available, Medium-term)
        $property2 = Property::updateOrCreate(
            [
                'title' => 'Mid-range Test House - Flexible Dates',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'house',
                'description' => 'Mid-range test property with flexible booking policies. Good for testing multi-week bookings and price calculations.',
                'street_address' => '200 Test Avenue',
                'city' => 'Cluj-Napoca',
                'state' => 'Cluj County',
                'country' => 'Romania',
                'postal_code' => '400001',
                'latitude' => 46.7712,
                'longitude' => 23.6236,
                'price_per_night' => 100.00,
                'price_per_week' => 600.00,
                'price_per_month' => 2000.00,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area_sqm' => 80,
                'square_footage' => 861,
                'guests' => 4,
                'min_nights' => 2,
                'max_nights' => 90,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
            ]
        );
        if (!empty($basicAmenities)) {
            $property2->amenities()->sync(array_slice($basicAmenities, 0, 5));
        }

        // Test Property 3: Premium Villa (Available, Long-term)
        $property3 = Property::updateOrCreate(
            [
                'title' => 'Premium Test Villa - High Capacity',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'villa',
                'description' => 'Premium test property for high-value bookings. Tests large group bookings, extended stays, and premium pricing.',
                'street_address' => '300 Test Boulevard',
                'city' => 'Constanța',
                'state' => 'Constanța County',
                'country' => 'Romania',
                'postal_code' => '900001',
                'latitude' => 44.1598,
                'longitude' => 28.6348,
                'price_per_night' => 250.00,
                'price_per_week' => 1500.00,
                'price_per_month' => 5000.00,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqm' => 200,
                'square_footage' => 2153,
                'guests' => 8,
                'min_nights' => 3,
                'max_nights' => 180,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
            ]
        );
        if (!empty($basicAmenities)) {
            $property3->amenities()->sync($basicAmenities);
        }

        // Test Property 4: Studio (Booked - for conflict testing)
        $property4 = Property::updateOrCreate(
            [
                'title' => 'Studio Test - Booking Conflicts',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'studio',
                'description' => 'Use this property to test booking conflicts and availability checking. Has existing bookings for testing overlap scenarios.',
                'street_address' => '400 Test Lane',
                'city' => 'Timișoara',
                'state' => 'Timiș County',
                'country' => 'Romania',
                'postal_code' => '300001',
                'latitude' => 45.7489,
                'longitude' => 21.2087,
                'price_per_night' => 75.00,
                'price_per_week' => 450.00,
                'price_per_month' => 1500.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqm' => 35,
                'square_footage' => 377,
                'guests' => 2,
                'min_nights' => 1,
                'max_nights' => 60,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
            ]
        );
        if (!empty($basicAmenities)) {
            $property4->amenities()->sync(array_slice($basicAmenities, 0, 2));
        }

        // Test Property 5: Maintenance Property (Unavailable - for testing)
        $property5 = Property::updateOrCreate(
            [
                'title' => 'Maintenance Test Property - Unavailable',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'apartment',
                'description' => 'This property is marked as maintenance for testing error handling and status validation in booking flows.',
                'street_address' => '500 Test Circle',
                'city' => 'Iași',
                'state' => 'Iași County',
                'country' => 'Romania',
                'postal_code' => '700001',
                'latitude' => 47.1585,
                'longitude' => 27.6014,
                'price_per_night' => 60.00,
                'price_per_week' => 360.00,
                'price_per_month' => 1200.00,
                'bedrooms' => 2,
                'bathrooms' => 1,
                'area_sqm' => 60,
                'square_footage' => 646,
                'guests' => 3,
                'min_nights' => 2,
                'max_nights' => 30,
                'status' => 'maintenance',
                'is_featured' => false,
                'is_active' => false,
            ]
        );

        // Create a sample booking for conflict testing (on property 4)
        if (class_exists(\App\Models\Booking::class)) {
            \App\Models\Booking::updateOrCreate(
                [
                    'property_id' => $property4->id,
                    'check_in' => now()->addDays(10)->format('Y-m-d'),
                ],
                [
                    'user_id' => $guest->id,
                    'check_out' => now()->addDays(15)->format('Y-m-d'),
                    'nights' => 5,
                    'guests' => 2,
                    'price_per_night' => 75.00,
                    'subtotal' => 375.00,
                    'cleaning_fee' => 0.00,
                    'security_deposit' => 0.00,
                    'taxes' => 0.00,
                    'total_amount' => 375.00,
                    'total_price' => 375.00,
                    'guest_name' => $guest->name,
                    'guest_email' => $guest->email,
                    'guest_phone' => $guest->phone ?? '+40 700 000 001',
                    'status' => 'confirmed',
                    'payment_status' => 'paid',
                ]
            );
            $this->command->info('   📅 Created sample booking (conflict test)');
        }

        $this->command->newLine();
        $this->command->info('✅ Created 5 booking test properties:');
        $this->command->table(
            ['Property', 'Type', 'Price/Night', 'Status', 'Instant Booking'],
            [
                ['Budget Apartment', 'apartment', '€50', 'available', 'Yes'],
                ['Mid-range House', 'house', '€100', 'available', 'No'],
                ['Premium Villa', 'villa', '€250', 'available', 'No'],
                ['Studio (Conflict)', 'studio', '€75', 'available', 'Yes'],
                ['Maintenance', 'apartment', '€60', 'maintenance', 'No'],
            ]
        );

        $this->command->newLine();
        $this->command->info('👥 Test Users Created:');
        $this->command->info('   Owner: owner@renthub.test / password123');
        $this->command->info('   Guest: guest@renthub.test / password123');
        
        $this->command->newLine();
        $this->command->warn('🧪 Booking Test Scenarios:');
        $this->command->info('   1. Quick booking: Use Budget Apartment (instant, 1 night min)');
        $this->command->info('   2. Standard booking: Use Mid-range House (requires approval)');
        $this->command->info('   3. Premium booking: Use Premium Villa (large groups, long stays)');
        $this->command->info('   4. Conflict test: Try booking Studio on days 10-15 (should fail)');
        $this->command->info('   5. Error handling: Try booking Maintenance property (should fail)');
        
        $this->command->newLine();
        $this->command->info('📝 Sample API Test:');
        $this->command->info('   POST /api/v1/bookings');
        $this->command->info('   {');
        $this->command->info('     "property_id": ' . $property1->id . ',');
        $this->command->info('     "check_in": "' . now()->addDays(30)->format('Y-m-d') . '",');
        $this->command->info('     "check_out": "' . now()->addDays(33)->format('Y-m-d') . '",');
        $this->command->info('     "guests": 2');
        $this->command->info('   }');
        $this->command->newLine();
    }
}
