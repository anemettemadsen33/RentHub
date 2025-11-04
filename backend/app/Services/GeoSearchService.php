<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;

class GeoSearchService
{
    /**
     * Calculate distance between two coordinates using Haversine formula
     */
    public static function calculateDistance(
        float $lat1, 
        float $lon1, 
        float $lat2, 
        float $lon2
    ): float {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Search properties within radius
     */
    public static function searchWithinRadius(
        float $latitude,
        float $longitude,
        float $radiusKm,
        array $filters = []
    ) {
        $query = Property::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price_per_night', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_per_night', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        if (!empty($filters['guests'])) {
            $query->where('guests', '>=', $filters['guests']);
        }

        if (!empty($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenity_id', $filters['amenities']);
            });
        }

        // Bounding box optimization before Haversine
        $latDelta = $radiusKm / 111; // 1 degree latitude ≈ 111 km
        $lonDelta = $radiusKm / (111 * cos(deg2rad($latitude)));

        $query->whereBetween('latitude', [
            $latitude - $latDelta,
            $latitude + $latDelta
        ])->whereBetween('longitude', [
            $longitude - $lonDelta,
            $longitude + $lonDelta
        ]);

        // Get properties and calculate exact distance
        $properties = $query->get()->map(function ($property) use ($latitude, $longitude) {
            $property->distance = self::calculateDistance(
                $latitude,
                $longitude,
                $property->latitude,
                $property->longitude
            );
            return $property;
        })->filter(function ($property) use ($radiusKm) {
            return $property->distance <= $radiusKm;
        })->sortBy('distance')->values();

        return $properties;
    }

    /**
     * Search properties within bounds (for map view)
     */
    public static function searchWithinBounds(
        float $swLat, // Southwest latitude
        float $swLng, // Southwest longitude
        float $neLat, // Northeast latitude
        float $neLng, // Northeast longitude
        array $filters = []
    ) {
        $query = Property::query()
            ->where('is_active', true)
            ->where('status', 'published')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$swLat, $neLat])
            ->whereBetween('longitude', [$swLng, $neLng]);

        // Apply filters
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['min_price'])) {
            $query->where('price_per_night', '>=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $query->where('price_per_night', '<=', $filters['max_price']);
        }

        if (!empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        if (!empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        if (!empty($filters['guests'])) {
            $query->where('guests', '>=', $filters['guests']);
        }

        if (!empty($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenity_id', $filters['amenities']);
            });
        }

        return $query->get();
    }

    /**
     * Cluster properties for map markers
     */
    public static function clusterProperties(
        $properties,
        int $zoom,
        int $gridSize = 60
    ): array {
        if ($zoom >= 14) {
            // Don't cluster on high zoom levels
            return $properties->map(function ($property) {
                return [
                    'type' => 'property',
                    'id' => $property->id,
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                    'title' => $property->title,
                    'price' => $property->price_per_night,
                    'image' => $property->main_image,
                ];
            })->values()->toArray();
        }

        // Simple grid-based clustering
        $clusters = [];
        $gridLatSize = 180 / pow(2, $zoom);
        $gridLngSize = 360 / pow(2, $zoom);

        foreach ($properties as $property) {
            $gridLat = floor($property->latitude / $gridLatSize);
            $gridLng = floor($property->longitude / $gridLngSize);
            $key = "{$gridLat}_{$gridLng}";

            if (!isset($clusters[$key])) {
                $clusters[$key] = [
                    'type' => 'cluster',
                    'count' => 0,
                    'latitude' => 0,
                    'longitude' => 0,
                    'properties' => [],
                    'min_price' => null,
                ];
            }

            $clusters[$key]['count']++;
            $clusters[$key]['latitude'] += $property->latitude;
            $clusters[$key]['longitude'] += $property->longitude;
            $clusters[$key]['properties'][] = $property->id;
            
            if ($clusters[$key]['min_price'] === null || $property->price_per_night < $clusters[$key]['min_price']) {
                $clusters[$key]['min_price'] = $property->price_per_night;
            }
        }

        // Calculate average coordinates for clusters
        foreach ($clusters as &$cluster) {
            $cluster['latitude'] /= $cluster['count'];
            $cluster['longitude'] /= $cluster['count'];
            
            // Convert single-property clusters to property markers
            if ($cluster['count'] === 1) {
                $property = $properties->firstWhere('id', $cluster['properties'][0]);
                $cluster = [
                    'type' => 'property',
                    'id' => $property->id,
                    'latitude' => $property->latitude,
                    'longitude' => $property->longitude,
                    'title' => $property->title,
                    'price' => $property->price_per_night,
                    'image' => $property->main_image,
                ];
            }
        }

        return array_values($clusters);
    }
}
