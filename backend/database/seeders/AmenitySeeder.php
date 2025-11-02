<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amenity;

class AmenitySeeder extends Seeder
{
    public function run(): void
    {
        $amenities = [
            // Basic Amenities
            ['name' => 'Wi-Fi', 'category' => 'basic', 'icon' => 'wifi', 'is_popular' => true, 'sort_order' => 1],
            ['name' => 'Kitchen', 'category' => 'basic', 'icon' => 'utensils', 'is_popular' => true, 'sort_order' => 2],
            ['name' => 'Air Conditioning', 'category' => 'basic', 'icon' => 'snowflake', 'is_popular' => true, 'sort_order' => 3],
            ['name' => 'Heating', 'category' => 'basic', 'icon' => 'fire', 'is_popular' => true, 'sort_order' => 4],
            ['name' => 'TV', 'category' => 'basic', 'icon' => 'tv', 'is_popular' => true, 'sort_order' => 5],
            ['name' => 'Washing Machine', 'category' => 'basic', 'icon' => 'tshirt', 'is_popular' => false, 'sort_order' => 6],
            ['name' => 'Dryer', 'category' => 'basic', 'icon' => 'wind', 'is_popular' => false, 'sort_order' => 7],
            ['name' => 'Iron', 'category' => 'basic', 'icon' => 'compress', 'is_popular' => false, 'sort_order' => 8],
            ['name' => 'Hair Dryer', 'category' => 'basic', 'icon' => 'wind', 'is_popular' => false, 'sort_order' => 9],
            
            // Comfort
            ['name' => 'Bed Linens', 'category' => 'comfort', 'icon' => 'bed', 'is_popular' => true, 'sort_order' => 10],
            ['name' => 'Towels', 'category' => 'comfort', 'icon' => 'bath', 'is_popular' => true, 'sort_order' => 11],
            ['name' => 'Toiletries', 'category' => 'comfort', 'icon' => 'soap', 'is_popular' => false, 'sort_order' => 12],
            ['name' => 'Extra Pillows', 'category' => 'comfort', 'icon' => 'moon', 'is_popular' => false, 'sort_order' => 13],
            
            // Outdoor
            ['name' => 'Balcony', 'category' => 'outdoor', 'icon' => 'home', 'is_popular' => true, 'sort_order' => 20],
            ['name' => 'Garden', 'category' => 'outdoor', 'icon' => 'leaf', 'is_popular' => true, 'sort_order' => 21],
            ['name' => 'Terrace', 'category' => 'outdoor', 'icon' => 'sun', 'is_popular' => false, 'sort_order' => 22],
            ['name' => 'BBQ Grill', 'category' => 'outdoor', 'icon' => 'fire', 'is_popular' => false, 'sort_order' => 23],
            ['name' => 'Outdoor Furniture', 'category' => 'outdoor', 'icon' => 'chair', 'is_popular' => false, 'sort_order' => 24],
            
            // Luxury
            ['name' => 'Pool', 'category' => 'luxury', 'icon' => 'swimmer', 'is_popular' => true, 'sort_order' => 30],
            ['name' => 'Hot Tub', 'category' => 'luxury', 'icon' => 'hot-tub', 'is_popular' => true, 'sort_order' => 31],
            ['name' => 'Gym', 'category' => 'luxury', 'icon' => 'dumbbell', 'is_popular' => false, 'sort_order' => 32],
            ['name' => 'Sauna', 'category' => 'luxury', 'icon' => 'thermometer', 'is_popular' => false, 'sort_order' => 33],
            ['name' => 'Game Room', 'category' => 'luxury', 'icon' => 'gamepad', 'is_popular' => false, 'sort_order' => 34],
            
            // Transportation
            ['name' => 'Free Parking', 'category' => 'transportation', 'icon' => 'car', 'is_popular' => true, 'sort_order' => 40],
            ['name' => 'Garage', 'category' => 'transportation', 'icon' => 'warehouse', 'is_popular' => false, 'sort_order' => 41],
            ['name' => 'EV Charger', 'category' => 'transportation', 'icon' => 'bolt', 'is_popular' => false, 'sort_order' => 42],
            
            // Safety
            ['name' => 'Smoke Detector', 'category' => 'safety', 'icon' => 'shield', 'is_popular' => false, 'sort_order' => 50],
            ['name' => 'Carbon Monoxide Detector', 'category' => 'safety', 'icon' => 'shield-alt', 'is_popular' => false, 'sort_order' => 51],
            ['name' => 'Fire Extinguisher', 'category' => 'safety', 'icon' => 'fire-extinguisher', 'is_popular' => false, 'sort_order' => 52],
            ['name' => 'First Aid Kit', 'category' => 'safety', 'icon' => 'medkit', 'is_popular' => false, 'sort_order' => 53],
            ['name' => 'Security Cameras', 'category' => 'safety', 'icon' => 'camera', 'is_popular' => false, 'sort_order' => 54],
            
            // Accessibility
            ['name' => 'Wheelchair Accessible', 'category' => 'accessibility', 'icon' => 'wheelchair', 'is_popular' => false, 'sort_order' => 60],
            ['name' => 'Step-free Access', 'category' => 'accessibility', 'icon' => 'universal-access', 'is_popular' => false, 'sort_order' => 61],
            
            // Family
            ['name' => 'High Chair', 'category' => 'family', 'icon' => 'baby', 'is_popular' => false, 'sort_order' => 70],
            ['name' => 'Crib', 'category' => 'family', 'icon' => 'baby', 'is_popular' => false, 'sort_order' => 71],
            ['name' => 'Children\'s Books and Toys', 'category' => 'family', 'icon' => 'puzzle-piece', 'is_popular' => false, 'sort_order' => 72],
        ];

        foreach ($amenities as $amenity) {
            Amenity::create($amenity);
        }
    }
}
