<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Property extends Model
{
    protected $fillable = [
        'title',
        'description', 
        'type',
        'bedrooms',
        'bathrooms',
        'guests',
        'price_per_night',
        'cleaning_fee',
        'security_deposit',
        'street_address',
        'city',
        'state',
        'country',
        'postal_code',
        'latitude',
        'longitude',
        'area_sqm',
        'built_year',
        'is_active',
        'is_featured',
        'available_from',
        'available_until',
        'images',
        'main_image',
        'user_id'
    ];

    protected $casts = [
        'images' => 'array',
        'price_per_night' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    // Accessors & Mutators
    protected function fullAddress(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->street_address}, {$this->city}, {$this->state}, {$this->country}"
        );
    }

    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->where('is_approved', true)->avg('rating') ?: 0
        );
    }

    protected function totalReviews(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->where('is_approved', true)->count()
        );
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeAvailable($query, $checkIn = null, $checkOut = null)
    {
        if ($checkIn && $checkOut) {
            return $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                  ->where(function ($q2) use ($checkIn, $checkOut) {
                      $q2->whereBetween('check_in', [$checkIn, $checkOut])
                         ->orWhereBetween('check_out', [$checkIn, $checkOut])
                         ->orWhere(function ($q3) use ($checkIn, $checkOut) {
                             $q3->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                         });
                  });
            });
        }
        
        return $query;
    }

    public function scopeByLocation($query, $city = null, $country = null)
    {
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }
        
        if ($country) {
            $query->where('country', 'like', "%{$country}%");
        }
        
        return $query;
    }

    public function scopePriceBetween($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price_per_night', '>=', $minPrice);
        }
        
        if ($maxPrice) {
            $query->where('price_per_night', '<=', $maxPrice);
        }
        
        return $query;
    }
}
