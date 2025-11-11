<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'furnishing_status',
        'bedrooms',
        'bathrooms',
        'guests',
        'min_nights',
        'max_nights',
        'price_per_night',
        'price_per_week',
        'price_per_month',
        'cleaning_fee',
        'security_deposit',
        'street_address',
        'city',
        'state',
        'country',
        'postal_code',
        'location',
        'latitude',
        'longitude',
        'area_sqm',
        'square_footage',
        'built_year',
        'floor_number',
        'parking_available',
        'parking_spaces',
        'is_active',
        'is_featured',
        'status',
        'available_from',
        'available_until',
        'blocked_dates',
        'custom_pricing',
        'rules',
        'images',
        'main_image',
        'imported_from',
        'external_id',
        'user_id',
        'owner_id',
        'price',
    ];

    protected $casts = [
        'images' => 'array',
        'rules' => 'array',
        'blocked_dates' => 'array',
        'custom_pricing' => 'array',
        'price_per_night' => 'decimal:2',
        'price_per_week' => 'decimal:2',
        'price_per_month' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'parking_available' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
    ];

    protected $appends = ['price', 'owner_id'];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            // Map legacy statuses to current enum for compatibility
            if (isset($model->status)) {
                $map = [
                    'active' => 'available',
                    'published' => 'available',
                    'inactive' => 'maintenance',
                    'draft' => 'maintenance',
                ];
                $status = strtolower($model->status);
                if (isset($map[$status])) {
                    $model->status = $map[$status];
                }
            }
        });
    }

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

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function wishlists(): BelongsToMany
    {
        return $this->belongsToMany(Wishlist::class, 'wishlist_items')
            ->withTimestamps()
            ->withPivot(['notes', 'price_alert', 'notify_availability']);
    }

    public function externalCalendars(): HasMany
    {
        return $this->hasMany(ExternalCalendar::class);
    }

    public function verification(): HasOne
    {
        return $this->hasOne(PropertyVerification::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function priceSuggestions(): HasMany
    {
        return $this->hasMany(PriceSuggestion::class);
    }

    public function smartLocks(): HasMany
    {
        return $this->hasMany(SmartLock::class);
    }

    public function activeSmartLocks(): HasMany
    {
        return $this->hasMany(SmartLock::class)->where('status', 'active');
    }

    public function iotDevices(): HasMany
    {
        return $this->hasMany(IoTDevice::class);
    }

    public function activeIotDevices(): HasMany
    {
        return $this->hasMany(IoTDevice::class)->where('is_active', true);
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
            get: fn () => round((float) ($this->reviews()->where('is_approved', true)->avg('rating') ?: 0), 1)
        );
    }

    protected function totalReviews(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->reviews()->where('is_approved', true)->count()
        );
    }

    // Alias accessors for backwards compatibility with tests
    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->price_per_night,
            set: fn ($value) => [
                'price_per_night' => $value,
                'price' => $value,
            ]
        );
    }

    protected function ownerId(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->user_id,
            set: fn ($value) => [
                'user_id' => $value,
                // Persist owner_id column for backwards compatibility in tests
                'owner_id' => $value,
            ]
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

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    // Helper methods
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isInactive(): bool
    {
        return $this->status === 'inactive';
    }

    public function publish(): bool
    {
        return $this->update(['status' => 'published', 'is_active' => true]);
    }

    public function unpublish(): bool
    {
        return $this->update(['status' => 'inactive', 'is_active' => false]);
    }

    public function setToDraft(): bool
    {
        return $this->update(['status' => 'draft', 'is_active' => false]);
    }

    public function isDateBlocked($date): bool
    {
        if (! $this->blocked_dates) {
            return false;
        }

        return in_array($date, $this->blocked_dates);
    }

    public function blockDate($date): bool
    {
        $blockedDates = $this->blocked_dates ?? [];

        if (! in_array($date, $blockedDates)) {
            $blockedDates[] = $date;

            return $this->update(['blocked_dates' => $blockedDates]);
        }

        return false;
    }

    public function unblockDate($date): bool
    {
        $blockedDates = $this->blocked_dates ?? [];

        if (($key = array_search($date, $blockedDates)) !== false) {
            unset($blockedDates[$key]);

            return $this->update(['blocked_dates' => array_values($blockedDates)]);
        }

        return false;
    }

    public function getPriceForDate($date)
    {
        if ($this->custom_pricing && isset($this->custom_pricing[$date])) {
            return $this->custom_pricing[$date];
        }

        return $this->price_per_night;
    }

    public function setCustomPrice($date, $price): bool
    {
        $customPricing = $this->custom_pricing ?? [];
        $customPricing[$date] = $price;

        return $this->update(['custom_pricing' => $customPricing]);
    }

    public function removeCustomPrice($date): bool
    {
        $customPricing = $this->custom_pricing ?? [];

        if (isset($customPricing[$date])) {
            unset($customPricing[$date]);

            return $this->update(['custom_pricing' => $customPricing]);
        }

        return false;
    }
}
