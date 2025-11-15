<?php

namespace Database\Seeders;

use App\Models\Amenity;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test user first
        $user = User::firstOrCreate(
            ['email' => 'owner@test.com'],
            [
                'name' => 'Test Owner',
                'password' => bcrypt('password'),
                'role' => 'owner',
                'is_verified' => true,
                'verified_at' => now(),
            ]
        );

        // Create amenities
        $amenities = [
            ['name' => 'WiFi', 'slug' => 'wifi', 'category' => 'essentials', 'icon' => '📶', 'is_popular' => true, 'sort_order' => 1],
            ['name' => 'Parcare', 'slug' => 'parking', 'category' => 'transport', 'icon' => '🚗', 'is_popular' => true, 'sort_order' => 2],
            ['name' => 'Aer condiționat', 'slug' => 'air-conditioning', 'category' => 'climate', 'icon' => '❄️', 'is_popular' => true, 'sort_order' => 3],
            ['name' => 'Bucătărie', 'slug' => 'kitchen', 'category' => 'essentials', 'icon' => '🍳', 'is_popular' => true, 'sort_order' => 4],
            ['name' => 'Mașină de spălat', 'slug' => 'washing-machine', 'category' => 'laundry', 'icon' => '👕', 'is_popular' => false, 'sort_order' => 5],
        ];

        foreach ($amenities as $amenity) {
            $slug = \Illuminate\Support\Str::slug($amenity['name']);
            $data = $amenity;
            $data['slug'] = $slug;
            Amenity::firstOrCreate(['slug' => $slug], $data);
        }

        // Create test properties
        $properties = [
            [
                'title' => 'Apartament modern în Centrul Vechi',
                'description' => 'Un apartament frumos și modern situat în inima Centrului Vechi din București. Perfect pentru explorarea orașului.',
                'type' => 'apartment',
                'bedrooms' => 2,
                'bathrooms' => 1,
                'guests' => 4,
                'price_per_night' => 250.00,
                'cleaning_fee' => 50.00,
                'street_address' => 'Strada Lipscani 15',
                'city' => 'București',
                'state' => 'Bucuresti',
                'country' => 'România',
                'postal_code' => '030167',
                'area_sqm' => 65,
                'built_year' => 2020,
                'is_active' => true,
                'is_featured' => true,
                'user_id' => $user->id,
            ],
            [
                'title' => 'Casă de vacanță la munte',
                'description' => 'O casă tradițională românească situată în munții Carpați. Perfectă pentru o escapadă liniștită.',
                'type' => 'house',
                'bedrooms' => 3,
                'bathrooms' => 2,
                'guests' => 6,
                'price_per_night' => 180.00,
                'cleaning_fee' => 75.00,
                'street_address' => 'Strada Principală 42',
                'city' => 'Brașov',
                'state' => 'Brasov',
                'country' => 'România',
                'postal_code' => '500001',
                'area_sqm' => 120,
                'built_year' => 1995,
                'is_active' => true,
                'is_featured' => true,
                'user_id' => $user->id,
            ],
            [
                'title' => 'Studio modern lângă plajă',
                'description' => 'Studio modern cu vedere la mare, la doar 2 minute de plaja din Mamaia.',
                'type' => 'studio',
                'bedrooms' => 1,
                'bathrooms' => 1,
                'guests' => 2,
                'price_per_night' => 120.00,
                'cleaning_fee' => 30.00,
                'street_address' => 'Bulevardul Mamaia 103',
                'city' => 'Constanța',
                'state' => 'Constanta',
                'country' => 'România',
                'postal_code' => '900001',
                'area_sqm' => 35,
                'built_year' => 2018,
                'is_active' => true,
                'is_featured' => false,
                'user_id' => $user->id,
            ],
        ];

        foreach ($properties as $propertyData) {
            // Ensure status is available for public listing
            $propertyData['status'] = 'available';
            $propertyData['is_active'] = true;

            // Avoid duplicates on repeated seeds by using title as a natural key
            $property = Property::firstOrCreate(
                ['title' => $propertyData['title']],
                $propertyData
            );

            // Attach random amenities
            $randomAmenities = Amenity::inRandomOrder()->take(rand(2, 4))->get();
            $property->amenities()->sync($randomAmenities->pluck('id'));
        }
    }
}
