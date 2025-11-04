<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_insurance_id',
        'user_id',
        'claim_number',
        'type',
        'status',
        'description',
        'claimed_amount',
        'approved_amount',
        'incident_date',
        'supporting_documents',
        'admin_notes',
        'submitted_at',
        'reviewed_at',
        'resolved_at',
        'reviewed_by',
    ];

    protected $casts = [
        'claimed_amount' => 'decimal:2',
        'approved_amount' => 'decimal:2',
        'supporting_documents' => 'array',
        'incident_date' => 'date',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($claim) {
            if (empty($claim->claim_number)) {
                $claim->claim_number = self::generateClaimNumber();
            }
            if (empty($claim->submitted_at)) {
                $claim->submitted_at = now();
            }
        });
    }

    public function bookingInsurance(): BelongsTo
    {
        return $this->belongsTo(BookingInsurance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public static function generateClaimNumber(): string
    {
        do {
            $claimNumber = 'CLM-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } while (self::where('claim_number', $claimNumber)->exists());

        return $claimNumber;
    }

    public function approve(float $approvedAmount, ?string $notes = null, ?int $reviewedBy = null): bool
    {
        if ($this->status !== 'under_review') {
            return false;
        }

        $this->update([
            'status' => 'approved',
            'approved_amount' => $approvedAmount,
            'admin_notes' => $notes,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy ?? auth()->id(),
        ]);

        return true;
    }

    public function reject(?string $notes = null, ?int $reviewedBy = null): bool
    {
        if (!in_array($this->status, ['submitted', 'under_review'])) {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'admin_notes' => $notes,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewedBy ?? auth()->id(),
        ]);

        return true;
    }

    public function markAsPaid(?string $notes = null): bool
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $this->update([
            'status' => 'paid',
            'resolved_at' => now(),
            'admin_notes' => ($this->admin_notes ?? '') . "\n\n" . ($notes ?? ''),
        ]);

        return true;
    }

    public function putUnderReview(?int $reviewedBy = null): bool
    {
        if ($this->status !== 'submitted') {
            return false;
        }

        $this->update([
            'status' => 'under_review',
            'reviewed_by' => $reviewedBy ?? auth()->id(),
        ]);

        return true;
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeUnderReview($query)
    {
        return $query->where('status', 'under_review');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }
}
