<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConciergeService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'service_provider_id',
        'name',
        'description',
        'service_type',
        'base_price',
        'price_unit',
        'duration_minutes',
        'max_guests',
        'pricing_extras',
        'requirements',
        'images',
        'is_available',
        'advance_booking_hours',
    ];

    protected $casts = [
        'pricing_extras' => 'array',
        'requirements' => 'array',
        'images' => 'array',
        'is_available' => 'boolean',
        'base_price' => 'decimal:2',
        'duration_minutes' => 'integer',
        'max_guests' => 'integer',
        'advance_booking_hours' => 'integer',
    ];

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ConciergeBooking::class);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->base_price, 2) . ' ' . $this->price_unit;
    }

    public function getEstimatedDurationAttribute(): string
    {
        if (!$this->duration_minutes) {
            return 'Variable';
        }
        
        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        if ($hours > 0 && $minutes > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$minutes}m";
        }
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('service_type', $type);
    }
}
