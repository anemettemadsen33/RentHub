<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GuestScreening extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'reviewed_by',
        'status',
        'risk_level',
        'screening_score',
        'identity_verified',
        'identity_verified_at',
        'identity_verification_method',
        'phone_verified',
        'phone_verified_at',
        'email_verified',
        'email_verified_at',
        'credit_check_completed',
        'credit_check_completed_at',
        'credit_score',
        'credit_rating',
        'background_check_completed',
        'background_check_completed_at',
        'background_check_passed',
        'references_count',
        'references_verified',
        'average_rating',
        'total_bookings',
        'completed_bookings',
        'cancelled_bookings',
        'admin_notes',
        'rejection_reason',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'identity_verified' => 'boolean',
        'identity_verified_at' => 'datetime',
        'phone_verified' => 'boolean',
        'phone_verified_at' => 'datetime',
        'email_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'credit_check_completed' => 'boolean',
        'credit_check_completed_at' => 'datetime',
        'background_check_completed' => 'boolean',
        'background_check_completed_at' => 'datetime',
        'background_check_passed' => 'boolean',
        'screening_score' => 'integer',
        'references_count' => 'integer',
        'references_verified' => 'integer',
        'average_rating' => 'decimal:2',
        'total_bookings' => 'integer',
        'completed_bookings' => 'integer',
        'cancelled_bookings' => 'integer',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ScreeningDocument::class);
    }

    public function creditCheck(): HasOne
    {
        return $this->hasOne(CreditCheck::class);
    }

    public function references(): HasMany
    {
        return $this->hasMany(GuestReference::class);
    }

    public function calculateScreeningScore(): int
    {
        $score = 0;

        // Identity verification (20 points)
        if ($this->identity_verified) {
            $score += 20;
        }

        // Phone verification (10 points)
        if ($this->phone_verified) {
            $score += 10;
        }

        // Email verification (10 points)
        if ($this->email_verified) {
            $score += 10;
        }

        // Credit check (25 points)
        if ($this->credit_check_completed && $this->credit_rating) {
            $score += match ($this->credit_rating) {
                'excellent' => 25,
                'good' => 20,
                'fair' => 15,
                'poor' => 5,
                default => 0
            };
        }

        // Background check (15 points)
        if ($this->background_check_completed && $this->background_check_passed) {
            $score += 15;
        }

        // References (20 points)
        if ($this->references_verified > 0) {
            $score += min(20, $this->references_verified * 7);
        }

        return min(100, $score);
    }

    public function determineRiskLevel(): string
    {
        $score = $this->screening_score ?? $this->calculateScreeningScore();

        return match (true) {
            $score >= 80 => 'low',
            $score >= 60 => 'medium',
            $score >= 40 => 'high',
            default => 'high'
        };
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'expired')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }
}
