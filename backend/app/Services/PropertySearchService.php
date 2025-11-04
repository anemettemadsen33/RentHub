<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Database\Eloquent\Builder;

class PropertySearchService
{
    public function search(array $filters): Builder
    {
        $query = Property::query()->where('status', 'published');

        // Location search
        if (! empty($filters['location'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('address', 'like', '%'.$filters['location'].'%')
                    ->orWhere('city', 'like', '%'.$filters['location'].'%')
                    ->orWhere('state', 'like', '%'.$filters['location'].'%');
            });
        }

        // Property type filter
        if (! empty($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }

        // Price range filter
        if (! empty($filters['min_price'])) {
            $query->where('price_per_night', '>=', $filters['min_price']);
        }
        if (! empty($filters['max_price'])) {
            $query->where('price_per_night', '<=', $filters['max_price']);
        }

        // Guest capacity
        if (! empty($filters['guests'])) {
            $query->where('max_guests', '>=', $filters['guests']);
        }

        // Bedrooms filter
        if (! empty($filters['bedrooms'])) {
            $query->where('bedrooms', '>=', $filters['bedrooms']);
        }

        // Bathrooms filter
        if (! empty($filters['bathrooms'])) {
            $query->where('bathrooms', '>=', $filters['bathrooms']);
        }

        // Amenities filter
        if (! empty($filters['amenities']) && is_array($filters['amenities'])) {
            $query->whereHas('amenities', function ($q) use ($filters) {
                $q->whereIn('amenity_id', $filters['amenities']);
            });
        }

        // Date availability
        if (! empty($filters['check_in']) && ! empty($filters['check_out'])) {
            $query->whereDoesntHave('bookings', function ($q) use ($filters) {
                $q->where(function ($subQ) use ($filters) {
                    $subQ->whereBetween('check_in_date', [$filters['check_in'], $filters['check_out']])
                        ->orWhereBetween('check_out_date', [$filters['check_in'], $filters['check_out']])
                        ->orWhere(function ($dateQ) use ($filters) {
                            $dateQ->where('check_in_date', '<=', $filters['check_in'])
                                ->where('check_out_date', '>=', $filters['check_out']);
                        });
                })->whereIn('status', ['confirmed', 'checked_in']);
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price_per_night', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_night', 'desc');
                break;
            case 'rating':
                $query->orderBy('average_rating', 'desc');
                break;
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            default:
                $query->orderBy($sortBy, $sortOrder);
        }

        return $query;
    }

    public function searchOnMap(array $filters): array
    {
        $properties = $this->search($filters)->get();

        return $properties->map(function ($property) {
            return [
                'id' => $property->id,
                'title' => $property->title,
                'price' => $property->price_per_night,
                'latitude' => $property->latitude,
                'longitude' => $property->longitude,
                'image' => $property->featured_image,
            ];
        })->toArray();
    }
}
