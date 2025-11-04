<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ConciergeService;
use App\Models\ServiceProvider;

class ConciergeServiceSeeder extends Seeder
{
    public function run(): void
    {
        // Create Service Providers first
        $providers = [
            [
                'name' => 'Michael Anderson',
                'company_name' => 'Elite Transport Services',
                'type' => 'concierge',
                'email' => 'contact@elitetransport.ro',
                'phone' => '+40721234567',
                'address' => 'Str. Aviatorilor 15, Bucharest',
                'city' => 'Bucharest',
                'zip_code' => '010091',
                'bio' => 'Premium airport transfers and luxury car services',
                'services_offered' => ['airport_pickup', 'car_rental'],
                'verified' => true,
                'status' => 'active',
                'average_rating' => 4.8,
            ],
            [
                'name' => 'Elena Popescu',
                'company_name' => 'Fresh Basket Delivery',
                'type' => 'concierge',
                'email' => 'orders@freshbasket.ro',
                'phone' => '+40721234568',
                'address' => 'Str. Polonă 45, Bucharest',
                'city' => 'Bucharest',
                'zip_code' => '010092',
                'bio' => 'Fresh groceries and specialty items delivered to your door',
                'services_offered' => ['grocery_delivery'],
                'verified' => true,
                'status' => 'active',
                'average_rating' => 4.9,
            ],
            [
                'name' => 'Alex Ionescu',
                'company_name' => 'Bucharest Tours & Experiences',
                'type' => 'concierge',
                'email' => 'hello@bucharesttours.ro',
                'phone' => '+40721234569',
                'address' => 'Bulevardul Unirii 34, Bucharest',
                'city' => 'Bucharest',
                'zip_code' => '030167',
                'bio' => 'Curated local experiences and guided tours',
                'services_offered' => ['local_experience'],
                'verified' => true,
                'status' => 'active',
                'average_rating' => 4.7,
            ],
            [
                'name' => 'Chef Giovanni Rossi',
                'company_name' => 'Chef at Home',
                'type' => 'concierge',
                'email' => 'bookings@chefathome.ro',
                'phone' => '+40721234570',
                'address' => 'Calea Victoriei 120, Bucharest',
                'city' => 'Bucharest',
                'zip_code' => '010093',
                'bio' => 'Private chefs for in-home dining experiences',
                'services_offered' => ['personal_chef'],
                'verified' => true,
                'status' => 'active',
                'average_rating' => 4.9,
            ],
            [
                'name' => 'Maria Vlad',
                'company_name' => 'Serenity Spa Mobile',
                'type' => 'concierge',
                'email' => 'spa@serenity.ro',
                'phone' => '+40721234571',
                'address' => 'Strada Lipscani 78, Bucharest',
                'city' => 'Bucharest',
                'zip_code' => '030167',
                'bio' => 'Professional spa treatments in the comfort of your property',
                'services_offered' => ['spa_service'],
                'verified' => true,
                'status' => 'active',
                'average_rating' => 4.8,
            ],
        ];

        $createdProviders = [];
        foreach ($providers as $providerData) {
            $provider = ServiceProvider::where('email', $providerData['email'])->first();
            if (!$provider) {
                $provider = ServiceProvider::create($providerData);
            }
            $createdProviders[] = $provider;
        }

        // Create Concierge Services
        $services = [
            // Airport Pickup Services
            [
                'service_provider_id' => $createdProviders[0]->id,
                'name' => 'Airport Transfer - Standard',
                'description' => 'Comfortable sedan transfer from/to Bucharest airport (Henri Coandă). Professional driver, meet & greet service included.',
                'service_type' => 'airport_pickup',
                'base_price' => 150.00,
                'price_unit' => 'per trip',
                'duration_minutes' => 60,
                'max_guests' => 3,
                'pricing_extras' => [
                    ['name' => 'Extra luggage (4+ bags)', 'price' => 20],
                    ['name' => 'Child seat', 'price' => 15],
                    ['name' => 'Pet transport', 'price' => 25],
                ],
                'requirements' => [
                    'Flight number required',
                    'Arrival time must be provided',
                    'Phone number for driver contact',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1449965408869-eaa3f722e40d?w=800',
                    'https://images.unsplash.com/photo-1552799446-159ba9523315?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 12,
            ],
            [
                'service_provider_id' => $createdProviders[0]->id,
                'name' => 'Airport Transfer - Luxury',
                'description' => 'Premium Mercedes-Benz E-Class transfer with luxury amenities. Complimentary refreshments and Wi-Fi.',
                'service_type' => 'airport_pickup',
                'base_price' => 250.00,
                'price_unit' => 'per trip',
                'duration_minutes' => 60,
                'max_guests' => 3,
                'pricing_extras' => [
                    ['name' => 'Champagne service', 'price' => 50],
                    ['name' => 'Extra stop en route', 'price' => 30],
                ],
                'requirements' => [
                    'Flight number required',
                    '24 hours advance booking',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1563720360172-67b8f3dce741?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 24,
            ],

            // Grocery Delivery
            [
                'service_provider_id' => $createdProviders[1]->id,
                'name' => 'Grocery Essentials Package',
                'description' => 'Pre-arrival grocery delivery with essentials: bread, milk, eggs, coffee, fruits, vegetables, and basic pantry items.',
                'service_type' => 'grocery_delivery',
                'base_price' => 120.00,
                'price_unit' => 'per delivery',
                'duration_minutes' => 30,
                'max_guests' => null,
                'pricing_extras' => [
                    ['name' => 'Premium package upgrade', 'price' => 50],
                    ['name' => 'Wine selection (3 bottles)', 'price' => 100],
                    ['name' => 'Custom shopping list', 'price' => 0],
                ],
                'requirements' => [
                    'Delivery address required',
                    'Preferred delivery time',
                    'Dietary restrictions (if any)',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1542838132-92c53300491e?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 24,
            ],
            [
                'service_provider_id' => $createdProviders[1]->id,
                'name' => 'Custom Grocery Shopping',
                'description' => 'Personal shopper will purchase items from your custom list. Perfect for specific dietary needs or preferences.',
                'service_type' => 'grocery_delivery',
                'base_price' => 50.00,
                'price_unit' => 'service fee + cost of items',
                'duration_minutes' => 60,
                'max_guests' => null,
                'pricing_extras' => [
                    ['name' => 'Express delivery (within 2 hours)', 'price' => 30],
                ],
                'requirements' => [
                    'Shopping list must be provided',
                    'Budget limit (optional)',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1534723452862-4c874018d66d?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 6,
            ],

            // Local Experiences
            [
                'service_provider_id' => $createdProviders[2]->id,
                'name' => 'Old Town Walking Tour',
                'description' => 'Discover Bucharest\'s historic center with a knowledgeable local guide. Visit Palace of Parliament, Stavropoleos Church, and more.',
                'service_type' => 'local_experience',
                'base_price' => 200.00,
                'price_unit' => 'per group (up to 6)',
                'duration_minutes' => 180,
                'max_guests' => 6,
                'pricing_extras' => [
                    ['name' => 'Museum entry tickets', 'price' => 50],
                    ['name' => 'Traditional lunch included', 'price' => 150],
                ],
                'requirements' => [
                    'Comfortable walking shoes recommended',
                    'Start time must be 9 AM - 3 PM',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1555881400-74d7acaacd8b?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 48,
            ],
            [
                'service_provider_id' => $createdProviders[2]->id,
                'name' => 'Wine Tasting Experience',
                'description' => 'Visit a local winery or enjoy an in-home Romanian wine tasting with sommelier. Learn about Romanian wine regions and varieties.',
                'service_type' => 'local_experience',
                'base_price' => 350.00,
                'price_unit' => 'per group (up to 8)',
                'duration_minutes' => 150,
                'max_guests' => 8,
                'pricing_extras' => [
                    ['name' => 'Premium wine selection', 'price' => 100],
                    ['name' => 'Cheese platter', 'price' => 80],
                ],
                'requirements' => [
                    'Participants must be 18+',
                    'Location preference (winery or property)',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1510812431401-41d2bd2722f3?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 72,
            ],

            // Personal Chef
            [
                'service_provider_id' => $createdProviders[3]->id,
                'name' => 'Private Chef - 3-Course Dinner',
                'description' => 'Professional chef prepares a custom 3-course meal in your property. Menu tailored to your preferences. Chef handles all cooking and cleanup.',
                'service_type' => 'personal_chef',
                'base_price' => 500.00,
                'price_unit' => 'per group (up to 6)',
                'duration_minutes' => 180,
                'max_guests' => 6,
                'pricing_extras' => [
                    ['name' => 'Additional guest (beyond 6)', 'price' => 60],
                    ['name' => 'Wine pairing', 'price' => 120],
                    ['name' => 'Premium ingredients upgrade', 'price' => 150],
                ],
                'requirements' => [
                    'Dietary restrictions/allergies',
                    'Cuisine preference',
                    'Functional kitchen required',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 48,
            ],
            [
                'service_provider_id' => $createdProviders[3]->id,
                'name' => 'Breakfast Service (Daily)',
                'description' => 'Wake up to a freshly prepared breakfast each morning. Continental or full breakfast options available.',
                'service_type' => 'personal_chef',
                'base_price' => 80.00,
                'price_unit' => 'per person per day',
                'duration_minutes' => 60,
                'max_guests' => 8,
                'pricing_extras' => [
                    ['name' => 'Full English breakfast', 'price' => 20],
                    ['name' => 'Fresh juice selection', 'price' => 15],
                ],
                'requirements' => [
                    'Breakfast time preference',
                    'Number of days needed',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1533089860892-a7c6f0a88666?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 24,
            ],

            // Spa Services
            [
                'service_provider_id' => $createdProviders[4]->id,
                'name' => 'Relaxation Massage (60 min)',
                'description' => 'Professional therapeutic massage in the comfort of your property. All equipment and aromatherapy oils provided.',
                'service_type' => 'spa_service',
                'base_price' => 250.00,
                'price_unit' => 'per person',
                'duration_minutes' => 90,
                'max_guests' => 1,
                'pricing_extras' => [
                    ['name' => '90-minute session upgrade', 'price' => 100],
                    ['name' => 'Hot stone therapy', 'price' => 50],
                    ['name' => 'Couples massage (2 therapists)', 'price' => 250],
                ],
                'requirements' => [
                    'Private quiet room required',
                    'Preferred massage type',
                    'Any injuries or conditions to note',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1544161515-4ab6ce6db874?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 24,
            ],
            [
                'service_provider_id' => $createdProviders[4]->id,
                'name' => 'Spa Day Package',
                'description' => 'Complete pampering experience: massage, facial, manicure, and pedicure. 4 hours of luxury treatments.',
                'service_type' => 'spa_service',
                'base_price' => 600.00,
                'price_unit' => 'per person',
                'duration_minutes' => 240,
                'max_guests' => 2,
                'pricing_extras' => [
                    ['name' => 'Body scrub treatment', 'price' => 100],
                    ['name' => 'Hair styling', 'price' => 80],
                ],
                'requirements' => [
                    'Suitable space required',
                    'Minimum 48 hours notice',
                ],
                'images' => [
                    'https://images.unsplash.com/photo-1560750588-73207b1ef5b8?w=800',
                ],
                'is_available' => true,
                'advance_booking_hours' => 48,
            ],
        ];

        foreach ($services as $serviceData) {
            ConciergeService::create($serviceData);
        }

        $this->command->info('✅ Created ' . count($services) . ' concierge services with ' . count($providers) . ' service providers');
    }
}
