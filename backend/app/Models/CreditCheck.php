<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditCheck extends Model
{
    protected $fillable = [
        'guest_screening_id',
        'user_id',
        'requested_by',
        'provider',
        'provider_reference',
        'credit_score',
        'max_score',
        'credit_rating',
        'report_data',
        'total_accounts',
        'open_accounts',
        'total_debt',
        'available_credit',
        'credit_utilization',
        'on_time_payments',
        'late_payments',
        'missed_payments',
        'defaults',
        'bankruptcies',
        'status',
        'passed',
        'failure_reason',
        'cost',
        'currency',
        'requested_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'credit_score' => 'integer',
        'max_score' => 'integer',
        'report_data' => 'array',
        'total_accounts' => 'integer',
        'open_accounts' => 'integer',
        'total_debt' => 'decimal:2',
        'available_credit' => 'decimal:2',
        'credit_utilization' => 'decimal:2',
        'on_time_payments' => 'integer',
        'late_payments' => 'integer',
        'missed_payments' => 'integer',
        'defaults' => 'integer',
        'bankruptcies' => 'integer',
        'passed' => 'boolean',
        'cost' => 'decimal:2',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function screening(): BelongsTo
    {
        return $this->belongsTo(GuestScreening::class, 'guest_screening_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function calculateCreditRating(): string
    {
        if (! $this->credit_score) {
            return 'none';
        }

        return match (true) {
            $this->credit_score >= 750 => 'excellent',
            $this->credit_score >= 700 => 'good',
            $this->credit_score >= 650 => 'fair',
            $this->credit_score >= 600 => 'poor',
            default => 'very_poor'
        };
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePassed($query)
    {
        return $query->where('passed', true);
    }
}
