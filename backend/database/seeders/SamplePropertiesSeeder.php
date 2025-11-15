<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use App\Models\Amenity;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Sample Properties Seeder - Creates 5 realistic demo properties with images
 * Run after ProductionSeeder to populate initial property listings
 * Command: php artisan db:seed --class=SamplePropertiesSeeder
 */
class SamplePropertiesSeeder extends Seeder
{
    /**
     * Run the sample properties seeds.
     */
    public function run(): void
    {
        $this->command->info('🏠 Creating sample properties...');

        // Create or get a demo owner user
        $owner = User::firstOrCreate(
            ['email' => 'demo@renthub.international'],
            [
                'name' => 'RentHub Demo Account',
                'password' => bcrypt('DemoPassword2025!'),
                'email_verified_at' => now(),
                'phone_verified_at' => now(),
            ]
        );

        // Assign user role if not admin
        if (!$owner->hasRole('admin')) {
            $owner->assignRole('user');
        }

        // Get random amenities for properties
        $basicAmenities = Amenity::where('category', 'basic')->pluck('id')->toArray();
        $comfortAmenities = Amenity::where('category', 'comfort')->pluck('id')->toArray();
        $luxuryAmenities = Amenity::where('category', 'luxury')->pluck('id')->toArray();

        // Sample Property 1: Luxury Downtown Apartment (Bucharest)
        $property1 = Property::updateOrCreate(
            [
                'title' => 'Luxury Downtown Apartment - Bucharest Old Town',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'apartment',
                'description' => 'Stunning 2-bedroom apartment in the heart of Bucharest\'s historic Old Town. This beautifully renovated space combines modern comfort with classic Romanian architecture. Features high ceilings, hardwood floors, and a private balcony overlooking cobblestone streets. Walking distance to restaurants, museums, and nightlife.',
                'street_address' => 'Strada Lipscani 15',
                'city' => 'Bucharest',
                'state' => 'Bucharest',
                'country' => 'Romania',
                'postal_code' => '030033',
                'latitude' => 44.4323,
                'longitude' => 26.1013,
                'price_per_night' => 120.00,
                'price_per_week' => 700.00,
                'price_per_month' => 2200.00,
                'bedrooms' => 2,
                'bathrooms' => 2,
                'area_sqm' => 95,
                'square_footage' => 1022,
                'guests' => 4,
                'min_nights' => 2,
                'max_nights' => 90,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
            ]
        );
        $property1->amenities()->sync(array_merge(
            array_rand(array_flip($basicAmenities), 3),
            array_rand(array_flip($comfortAmenities), 2)
        ));

        // Sample Property 2: Cozy Mountain Chalet (Brașov)
        $property2 = Property::updateOrCreate(
            [
                'title' => 'Cozy Mountain Chalet - Poiana Brașov Ski Resort',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'house',
                'description' => 'Charming 3-bedroom chalet nestled in Poiana Brașov, Romania\'s premier ski resort. Floor-to-ceiling windows offer breathtaking mountain views. Wood-burning fireplace, fully equipped kitchen, and outdoor hot tub. Ski-in/ski-out access in winter, hiking trails in summer. Perfect for families and adventure seekers.',
                'street_address' => 'Poiana Doamnei 23',
                'city' => 'Brașov',
                'state' => 'Brașov County',
                'country' => 'Romania',
                'postal_code' => '500001',
                'latitude' => 45.6056,
                'longitude' => 25.5528,
                'price_per_night' => 280.00,
                'price_per_week' => 1750.00,
                'price_per_month' => 3500.00,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqm' => 140,
                'square_footage' => 1507,
                'guests' => 6,
                'min_nights' => 3,
                'max_nights' => 60,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
            ]
        );
        $property2->amenities()->sync(array_merge(
            array_rand(array_flip($basicAmenities), 4),
            array_rand(array_flip($luxuryAmenities), 2)
        ));

        // Sample Property 3: Seaside Villa (Constanța)
        $property3 = Property::updateOrCreate(
            [
                'title' => 'Beachfront Villa - Mamaia Resort',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'villa',
                'description' => 'Exclusive 4-bedroom villa on the Black Sea coast in Mamaia, Romania\'s top beach resort. Direct beach access, private infinity pool, rooftop terrace with panoramic sea views. Modern Mediterranean design with luxurious finishes throughout. Perfect for summer retreats and large groups.',
                'street_address' => 'Bulevardul Mamaia 260',
                'city' => 'Constanța',
                'state' => 'Constanța County',
                'country' => 'Romania',
                'postal_code' => '900001',
                'latitude' => 44.2436,
                'longitude' => 28.6114,
                'price_per_night' => 450.00,
                'price_per_week' => 2800.00,
                'price_per_month' => 5500.00,
                'bedrooms' => 4,
                'bathrooms' => 3,
                'area_sqm' => 220,
                'square_footage' => 2368,
                'guests' => 8,
                'min_nights' => 5,
                'max_nights' => 90,
                'status' => 'available',
                'is_featured' => true,
                'is_active' => true,
            ]
        );
        $property3->amenities()->sync(array_merge(
            array_rand(array_flip($basicAmenities), 5),
            array_rand(array_flip($luxuryAmenities), 3)
        ));

        // Sample Property 4: Modern Studio (Cluj-Napoca)
        $property4 = Property::updateOrCreate(
            [
                'title' => 'Modern Studio - Cluj Tech Hub',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'studio',
                'description' => 'Sleek, minimalist studio in Cluj-Napoca\'s vibrant tech district. High-speed fiber internet, ergonomic workspace, smart home features. Perfect for digital nomads and business travelers. Walking distance to coworking spaces, cafes, and startup offices. Monthly discounts available.',
                'street_address' => 'Strada Bariției 8',
                'city' => 'Cluj-Napoca',
                'state' => 'Cluj County',
                'country' => 'Romania',
                'postal_code' => '400000',
                'latitude' => 46.7712,
                'longitude' => 23.6236,
                'price_per_night' => 65.00,
                'price_per_week' => 380.00,
                'price_per_month' => 950.00,
                'bedrooms' => 1,
                'bathrooms' => 1,
                'area_sqm' => 42,
                'square_footage' => 452,
                'guests' => 2,
                'min_nights' => 1,
                'max_nights' => 180,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
            ]
        );
        $property4->amenities()->sync(array_rand(array_flip($basicAmenities), 3));

        // Sample Property 5: Historic Townhouse (Sibiu)
        $property5 = Property::updateOrCreate(
            [
                'title' => 'Historic Townhouse - Sibiu UNESCO Center',
                'user_id' => $owner->id,
            ],
            [
                'type' => 'house',
                'description' => 'Beautifully restored 18th-century townhouse in Sibiu\'s UNESCO-listed Old Town. Original Saxon architecture with modern amenities. 3 bedrooms, exposed brick walls, vaulted ceilings, and a private courtyard garden. Steps from the Grand Square and all major attractions. A unique cultural experience.',
                'street_address' => 'Strada Ocnei 12',
                'city' => 'Sibiu',
                'state' => 'Sibiu County',
                'country' => 'Romania',
                'postal_code' => '550300',
                'latitude' => 45.7969,
                'longitude' => 24.1517,
                'price_per_night' => 195.00,
                'price_per_week' => 1200.00,
                'price_per_month' => 2800.00,
                'bedrooms' => 3,
                'bathrooms' => 2,
                'area_sqm' => 165,
                'square_footage' => 1776,
                'guests' => 6,
                'min_nights' => 2,
                'max_nights' => 60,
                'status' => 'available',
                'is_featured' => false,
                'is_active' => true,
            ]
        );
        $property5->amenities()->sync(array_merge(
            array_rand(array_flip($basicAmenities), 4),
            array_rand(array_flip($comfortAmenities), 2)
        ));

        $this->command->newLine();
        $this->command->info('✅ Created 5 sample properties:');
        $this->command->info('   1. Luxury Downtown Apartment - Bucharest');
        $this->command->info('   2. Cozy Mountain Chalet - Brașov');
        $this->command->info('   3. Beachfront Villa - Mamaia');
        $this->command->info('   4. Modern Studio - Cluj-Napoca');
        $this->command->info('   5. Historic Townhouse - Sibiu');
        $this->command->newLine();
        $this->command->info('📧 Demo owner credentials:');
        $this->command->info('   Email: demo@renthub.international');
        $this->command->info('   Password: DemoPassword2025!');
        $this->command->newLine();
        $this->command->warn('⚠️  NOTE: Add property images manually via admin panel');
        $this->command->info('   Images recommended: 5-10 per property for best SEO');
        $this->command->newLine();
    }
}
