<?php

namespace App\Services;

use App\Models\Property;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PropertyImportService
{
    /**
     * Import property from external platform
     *
     * @param  string  $platform  ('booking', 'airbnb', 'vrbo')
     */
    public function importProperty(string $platform, string $url, User $user): array
    {
        try {
            // Validate URL format
            $this->validateUrl($platform, $url);

            // Extract property data based on platform
            $propertyData = match ($platform) {
                'booking' => $this->importFromBooking($url),
                'airbnb' => $this->importFromAirbnb($url),
                'vrbo' => $this->importFromVrbo($url),
                default => throw new \InvalidArgumentException('Unsupported platform'),
            };

            // Create property in database
            $property = $this->createProperty($propertyData, $user);

            return [
                'success' => true,
                'property_id' => $property->id,
                'message' => 'Property imported successfully',
                'data' => $property->load(['amenities']),
            ];
        } catch (\Exception $e) {
            Log::error('Property import failed', [
                'platform' => $platform,
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Validate URL format for each platform
     */
    private function validateUrl(string $platform, string $url): void
    {
        $patterns = [
            'booking' => '/^https?:\/\/(www\.)?booking\.com\/.+/',
            'airbnb' => '/^https?:\/\/(www\.)?airbnb\.com\/rooms\/.+/',
            'vrbo' => '/^https?:\/\/(www\.)?vrbo\.com\/.+/',
        ];

        if (! preg_match($patterns[$platform], $url)) {
            throw new \InvalidArgumentException("Invalid {$platform} URL format");
        }
    }

    /**
     * Import from Booking.com
     */
    private function importFromBooking(string $url): array
    {
        // TODO: Implement actual Booking.com API integration
        // For now, return mock data structure

        // Extract property ID from URL
        preg_match('/hotel\/[a-z]+\/([^\.]+)/', $url, $matches);
        $propertySlug = $matches[1] ?? 'property';

        return [
            'platform' => 'booking',
            'external_id' => Str::random(10),
            'title' => ucwords(str_replace('-', ' ', $propertySlug)),
            'description' => 'Imported from Booking.com - Beautiful property with great amenities and perfect location.',
            'type' => 'apartment',
            'furnishing_status' => 'furnished',
            'bedrooms' => rand(1, 4),
            'bathrooms' => rand(1, 3),
            'guests' => rand(2, 8),
            'price_per_night' => rand(50, 300),
            'street_address' => '123 Import Street',
            'city' => 'Bucharest',
            'state' => 'Bucharest',
            'country' => 'Romania',
            'postal_code' => '010101',
            'latitude' => 44.4268,
            'longitude' => 26.1025,
            'amenities' => ['WiFi', 'Air Conditioning', 'Kitchen', 'Parking', 'TV'],
            'photos' => [
                'https://via.placeholder.com/800x600?text=Booking.com+Import+1',
                'https://via.placeholder.com/800x600?text=Booking.com+Import+2',
            ],
        ];
    }

    /**
     * Import from Airbnb
     */
    private function importFromAirbnb(string $url): array
    {
        // TODO: Implement actual Airbnb API integration

        // Extract room ID from URL
        preg_match('/rooms\/(\d+)/', $url, $matches);
        $roomId = $matches[1] ?? '12345';

        return [
            'platform' => 'airbnb',
            'external_id' => $roomId,
            'title' => 'Airbnb Imported Property #'.$roomId,
            'description' => 'Imported from Airbnb - Cozy and modern space perfect for your stay.',
            'type' => 'house',
            'furnishing_status' => 'furnished',
            'bedrooms' => rand(1, 5),
            'bathrooms' => rand(1, 3),
            'guests' => rand(2, 10),
            'price_per_night' => rand(60, 350),
            'street_address' => '456 Airbnb Avenue',
            'city' => 'Cluj-Napoca',
            'state' => 'Cluj',
            'country' => 'Romania',
            'postal_code' => '400001',
            'latitude' => 46.7712,
            'longitude' => 23.6236,
            'amenities' => ['WiFi', 'Heating', 'Kitchen', 'Washer', 'Dryer', 'TV'],
            'photos' => [
                'https://via.placeholder.com/800x600?text=Airbnb+Import+1',
                'https://via.placeholder.com/800x600?text=Airbnb+Import+2',
                'https://via.placeholder.com/800x600?text=Airbnb+Import+3',
            ],
        ];
    }

    /**
     * Import from VRBO
     */
    private function importFromVrbo(string $url): array
    {
        // TODO: Implement actual VRBO API integration

        // Extract property ID from URL
        preg_match('/\/(\d+)/', $url, $matches);
        $propertyId = $matches[1] ?? '67890';

        return [
            'platform' => 'vrbo',
            'external_id' => $propertyId,
            'title' => 'VRBO Vacation Rental #'.$propertyId,
            'description' => 'Imported from VRBO - Spacious vacation rental with stunning views.',
            'type' => 'villa',
            'furnishing_status' => 'furnished',
            'bedrooms' => rand(2, 6),
            'bathrooms' => rand(2, 4),
            'guests' => rand(4, 12),
            'price_per_night' => rand(100, 500),
            'street_address' => '789 Vacation Road',
            'city' => 'Brașov',
            'state' => 'Brașov',
            'country' => 'Romania',
            'postal_code' => '500001',
            'latitude' => 45.6579,
            'longitude' => 25.6012,
            'amenities' => ['WiFi', 'Pool', 'Hot Tub', 'BBQ', 'Garden', 'Parking', 'TV'],
            'photos' => [
                'https://via.placeholder.com/800x600?text=VRBO+Import+1',
                'https://via.placeholder.com/800x600?text=VRBO+Import+2',
                'https://via.placeholder.com/800x600?text=VRBO+Import+3',
                'https://via.placeholder.com/800x600?text=VRBO+Import+4',
            ],
        ];
    }

    /**
     * Create property from imported data
     */
    private function createProperty(array $data, User $user): Property
    {
        // Create main property record
        $property = Property::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'type' => $data['type'],
            'furnishing_status' => $data['furnishing_status'],
            'bedrooms' => $data['bedrooms'],
            'bathrooms' => $data['bathrooms'],
            'guests' => $data['guests'],
            'price_per_night' => $data['price_per_night'],
            'street_address' => $data['street_address'],
            'city' => $data['city'],
            'state' => $data['state'],
            'country' => $data['country'],
            'postal_code' => $data['postal_code'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'status' => 'maintenance', // Starts as maintenance for owner review
            'is_active' => false, // Not active until owner reviews and publishes
            'imported_from' => $data['platform'],
            'external_id' => $data['external_id'],
        ]);

        // Attach amenities if they exist
        if (! empty($data['amenities'])) {
            $this->attachAmenities($property, $data['amenities']);
        }

        // Create photo records if they exist
        // TODO: Uncomment when Property model has photos() relationship
        // if (!empty($data['photos'])) {
        //     $this->attachPhotos($property, $data['photos']);
        // }

        return $property;
    }

    /**
     * Attach amenities to property
     */
    private function attachAmenities(Property $property, array $amenities): void
    {
        // TODO: Map amenity names to IDs in your amenities table
        // For now, just log them
        Log::info('Amenities to attach', ['amenities' => $amenities]);
    }

    /**
     * Attach photos to property
     */
    private function attachPhotos(Property $property, array $photoUrls): void
    {
        foreach ($photoUrls as $index => $url) {
            // TODO: Download and store photos properly
            // For now, just create photo records with external URLs
            $property->photos()->create([
                'url' => $url,
                'order' => $index + 1,
                'is_primary' => $index === 0,
            ]);
        }
    }

    /**
     * Get import statistics for user
     */
    public function getImportStats(User $user): array
    {
        $properties = Property::where('user_id', $user->id)
            ->whereNotNull('imported_from')
            ->get();

        return [
            'total_imported' => $properties->count(),
            'by_platform' => [
                'booking' => $properties->where('imported_from', 'booking')->count(),
                'airbnb' => $properties->where('imported_from', 'airbnb')->count(),
                'vrbo' => $properties->where('imported_from', 'vrbo')->count(),
            ],
            'recent_imports' => $properties->sortByDesc('created_at')->take(5)->values(),
        ];
    }
}
