<?php

namespace App\Http\\Controllers\\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeoController extends Controller
{
    /**
     * Get all unique locations for sitemap generation
     */
    public function locations(): JsonResponse
    {
        $locations = Cache::remember('seo.locations', 3600, function () {
            return Property::select('city', 'state', 'country')
                ->where('status', 'active')
                ->distinct()
                ->get()
                ->map(function ($property) {
                    return $property->city.', '.$property->country;
                })
                ->unique()
                ->values()
                ->toArray();
        });

        return response()->json($locations);
    }

    /**
     * Get all property IDs with their last update date
     */
    public function propertyUrls(): JsonResponse
    {
        $properties = Cache::remember('seo.property_urls', 1800, function () {
            return Property::select('id', 'updated_at', 'created_at')
                ->where('status', 'active')
                ->get()
                ->map(function ($property) {
                    return [
                        'id' => $property->id,
                        'updated_at' => $property->updated_at->toIso8601String(),
                    ];
                })
                ->toArray();
        });

        return response()->json($properties);
    }

    /**
     * Get popular search queries for SEO
     */
    public function popularSearches(): JsonResponse
    {
        $searches = Cache::remember('seo.popular_searches', 3600, function () {
            // Get most common location searches
            $locationSearches = Property::select('city', DB::raw('COUNT(*) as count'))
                ->where('status', 'active')
                ->groupBy('city')
                ->orderByDesc('count')
                ->limit(20)
                ->get()
                ->map(fn ($item) => [
                    'query' => $item->city,
                    'type' => 'location',
                    'count' => $item->count,
                ])
                ->toArray();

            // Get property type searches
            $typeSearches = Property::select('property_type', DB::raw('COUNT(*) as count'))
                ->where('status', 'active')
                ->whereNotNull('property_type')
                ->groupBy('property_type')
                ->orderByDesc('count')
                ->get()
                ->map(fn ($item) => [
                    'query' => $item->property_type,
                    'type' => 'property_type',
                    'count' => $item->count,
                ])
                ->toArray();

            return array_merge($locationSearches, $typeSearches);
        });

        return response()->json($searches);
    }

    /**
     * Get SEO metadata for properties
     */
    public function propertyMetadata(int $id): JsonResponse
    {
        $property = Property::with(['images', 'reviews', 'amenities'])
            ->findOrFail($id);

        $metadata = [
            'id' => $property->id,
            'title' => $property->title,
            'description' => $property->description,
            'price' => $property->price,
            'location' => [
                'address' => $property->address,
                'city' => $property->city,
                'state' => $property->state,
                'country' => $property->country,
                'postalCode' => $property->postal_code,
            ],
            'images' => $property->images->map(fn ($img) => $img->image_url)->take(5)->toArray(),
            'bedrooms' => $property->bedrooms,
            'bathrooms' => $property->bathrooms,
            'area' => $property->square_feet,
            'amenities' => $property->amenities->pluck('name')->toArray(),
            'rating' => round($property->reviews->avg('rating'), 1),
            'reviewCount' => $property->reviews->count(),
            'updated_at' => $property->updated_at->toIso8601String(),
        ];

        return response()->json($metadata);
    }

    /**
     * Get structured data for organization
     */
    public function organizationData(): JsonResponse
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => 'RentHub',
            'url' => config('app.url'),
            'logo' => config('app.url').'/logo.png',
            'description' => 'Leading property rental platform for finding and booking rental properties worldwide',
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => '+1-555-RENTHUB',
                'contactType' => 'customer service',
                'areaServed' => 'US',
                'availableLanguage' => ['en', 'es', 'fr'],
            ],
        ];

        return response()->json($data);
    }

    /**
     * Clear SEO cache
     */
    public function clearCache(): JsonResponse
    {
        Cache::forget('seo.locations');
        Cache::forget('seo.property_urls');
        Cache::forget('seo.popular_searches');

        return response()->json(['message' => 'SEO cache cleared successfully']);
    }
}

