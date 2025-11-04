<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoyaltyTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'points',
        'booking_id',
        'description',
        'reference_type',
        'reference_id',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function scopeEarned($query)
    {
        return $query->where('type', 'earned');
    }

    public function scopeRedeemed($query)
    {
        return $query->where('type', 'redeemed');
    }

    public function scopeExpired($query)
    {
        return $query->where('type', 'expired')->orWhere('is_expired', true);
    }

    public function scopeBonus($query)
    {
        return $query->where('type', 'bonus');
    }

    public function scopeActive($query)
    {
        return $query->where('is_expired', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays($days)]);
    }
}
