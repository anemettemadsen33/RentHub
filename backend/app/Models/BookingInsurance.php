<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingInsurance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_id',
        'insurance_plan_id',
        'policy_number',
        'status',
        'premium_amount',
        'coverage_amount',
        'valid_from',
        'valid_until',
        'coverage_details',
        'policy_document_url',
        'activated_at',
    ];

    protected $casts = [
        'premium_amount' => 'decimal:2',
        'coverage_amount' => 'decimal:2',
        'coverage_details' => 'array',
        'policy_document_url' => 'array',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'activated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($bookingInsurance) {
            if (empty($bookingInsurance->policy_number)) {
                $bookingInsurance->policy_number = self::generatePolicyNumber();
            }
        });
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function insurancePlan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public static function generatePolicyNumber(): string
    {
        do {
            $policyNumber = 'INS-'.strtoupper(uniqid()).'-'.rand(1000, 9999);
        } while (self::where('policy_number', $policyNumber)->exists());

        return $policyNumber;
    }

    public function activate(): bool
    {
        if ($this->status === 'active') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'activated_at' => now(),
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (! in_array($this->status, ['pending', 'active'])) {
            return false;
        }

        $this->update(['status' => 'cancelled']);

        return true;
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->valid_from <= now()
            && $this->valid_until >= now();
    }

    public function isExpired(): bool
    {
        return $this->valid_until < now();
    }

    public function canBeClaimed(): bool
    {
        return $this->isActive() && $this->status !== 'claimed';
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_until', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->whereDate('valid_until', '<', now());
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
