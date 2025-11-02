<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'booking_id',
        'rating',
        'comment',
        'cleanliness_rating',
        'communication_rating',
        'check_in_rating',
        'accuracy_rating',
        'location_rating',
        'value_rating',
        'is_approved',
        'admin_notes',
        'owner_response',
        'owner_response_at'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'owner_response_at' => 'datetime',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    // Accessors
    public function getAverageDetailedRatingAttribute()
    {
        $ratings = [
            $this->cleanliness_rating,
            $this->communication_rating,
            $this->check_in_rating,
            $this->accuracy_rating,
            $this->location_rating,
            $this->value_rating
        ];

        $validRatings = array_filter($ratings, function($rating) {
            return $rating !== null;
        });

        return count($validRatings) > 0 ? array_sum($validRatings) / count($validRatings) : 0;
    }

    public function getHasOwnerResponseAttribute()
    {
        return !empty($this->owner_response);
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeWithResponse($query)
    {
        return $query->whereNotNull('owner_response');
    }

    public function scopeWithoutResponse($query)
    {
        return $query->whereNull('owner_response');
    }

    public function scopeHighRating($query, $minRating = 4)
    {
        return $query->where('rating', '>=', $minRating);
    }

    public function scopeLowRating($query, $maxRating = 2)
    {
        return $query->where('rating', '<=', $maxRating);
    }
}
