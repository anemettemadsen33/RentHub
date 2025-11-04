<?php

namespace Database\Seeders;

use App\Models\Amenity;
use Illuminate\Database\Seeder;

class AmenitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            // Essential
            ['name' => 'WiFi', 'icon' => 'wifi', 'category' => 'essential'],
            ['name' => 'Kitchen', 'icon' => 'utensils', 'category' => 'essential'],
            ['name' => 'Heating', 'icon' => 'fire', 'category' => 'essential'],
            ['name' => 'Air Conditioning', 'icon' => 'snowflake', 'category' => 'essential'],
            ['name' => 'Washer', 'icon' => 'washing-machine', 'category' => 'essential'],
            ['name' => 'Dryer', 'icon' => 'wind', 'category' => 'essential'],
            ['name' => 'Hot Water', 'icon' => 'droplet', 'category' => 'essential'],

            // Safety
            ['name' => 'Smoke Alarm', 'icon' => 'bell', 'category' => 'safety'],
            ['name' => 'Carbon Monoxide Alarm', 'icon' => 'alert-triangle', 'category' => 'safety'],
            ['name' => 'Fire Extinguisher', 'icon' => 'shield', 'category' => 'safety'],
            ['name' => 'First Aid Kit', 'icon' => 'heart', 'category' => 'safety'],

            // Entertainment
            ['name' => 'TV', 'icon' => 'tv', 'category' => 'entertainment'],
            ['name' => 'Cable TV', 'icon' => 'tv', 'category' => 'entertainment'],
            ['name' => 'Netflix', 'icon' => 'play', 'category' => 'entertainment'],
            ['name' => 'Gaming Console', 'icon' => 'gamepad', 'category' => 'entertainment'],
            ['name' => 'Books', 'icon' => 'book', 'category' => 'entertainment'],

            // Outdoor
            ['name' => 'Parking', 'icon' => 'car', 'category' => 'outdoor'],
            ['name' => 'Pool', 'icon' => 'pool', 'category' => 'outdoor'],
            ['name' => 'Hot Tub', 'icon' => 'bath', 'category' => 'outdoor'],
            ['name' => 'BBQ Grill', 'icon' => 'grill', 'category' => 'outdoor'],
            ['name' => 'Garden', 'icon' => 'tree', 'category' => 'outdoor'],
            ['name' => 'Balcony', 'icon' => 'balcony', 'category' => 'outdoor'],
            ['name' => 'Patio', 'icon' => 'chair', 'category' => 'outdoor'],

            // Family
            ['name' => 'Crib', 'icon' => 'baby', 'category' => 'family'],
            ['name' => 'High Chair', 'icon' => 'chair', 'category' => 'family'],
            ['name' => 'Children Books & Toys', 'icon' => 'toy', 'category' => 'family'],

            // Workspace
            ['name' => 'Dedicated Workspace', 'icon' => 'briefcase', 'category' => 'workspace'],
            ['name' => 'Desk', 'icon' => 'monitor', 'category' => 'workspace'],
            ['name' => 'Office Chair', 'icon' => 'chair', 'category' => 'workspace'],

            // Other
            ['name' => 'Gym', 'icon' => 'dumbbell', 'category' => 'other'],
            ['name' => 'Elevator', 'icon' => 'arrow-up', 'category' => 'other'],
            ['name' => 'Pet Friendly', 'icon' => 'paw', 'category' => 'other'],
            ['name' => 'Smoking Allowed', 'icon' => 'cigarette', 'category' => 'other'],
            ['name' => 'Long Term Stays', 'icon' => 'calendar', 'category' => 'other'],
            ['name' => 'Self Check-in', 'icon' => 'key', 'category' => 'other'],
            ['name' => '24/7 Security', 'icon' => 'shield', 'category' => 'other'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::updateOrCreate(
                ['name' => $amenity['name']],
                $amenity
            );
        }
    }
}
