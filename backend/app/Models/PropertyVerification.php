<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
        'ownership_status',
        'ownership_document_type',
        'ownership_documents',
        'ownership_verified_at',
        'ownership_rejection_reason',
        'inspection_status',
        'inspection_scheduled_at',
        'inspection_completed_at',
        'inspector_id',
        'inspection_report',
        'inspection_score',
        'inspection_notes',
        'photos_status',
        'photos_rejection_reason',
        'photos_verified_at',
        'details_status',
        'details_to_correct',
        'details_verified_at',
        'has_business_license',
        'business_license_document',
        'has_safety_certificate',
        'safety_certificate_document',
        'has_insurance',
        'insurance_document',
        'insurance_expiry_date',
        'overall_status',
        'has_verified_badge',
        'verification_score',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'next_verification_due',
        'last_verified_at',
    ];

    protected $casts = [
        'ownership_documents' => 'array',
        'ownership_verified_at' => 'datetime',
        'inspection_scheduled_at' => 'datetime',
        'inspection_completed_at' => 'datetime',
        'inspection_report' => 'array',
        'inspection_score' => 'integer',
        'photos_verified_at' => 'datetime',
        'details_to_correct' => 'array',
        'details_verified_at' => 'datetime',
        'has_business_license' => 'boolean',
        'has_safety_certificate' => 'boolean',
        'has_insurance' => 'boolean',
        'insurance_expiry_date' => 'date',
        'has_verified_badge' => 'boolean',
        'verification_score' => 'integer',
        'reviewed_at' => 'datetime',
        'next_verification_due' => 'date',
        'last_verified_at' => 'datetime',
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

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->morphMany(VerificationDocument::class, 'verifiable');
    }

    // Helper Methods
    public function calculateVerificationScore(): int
    {
        $score = 0;

        // Ownership verification (30 points)
        if ($this->ownership_status === 'approved') {
            $score += 30;
        }

        // Inspection (25 points)
        if ($this->inspection_status === 'completed') {
            $score += ($this->inspection_score ? ($this->inspection_score * 0.25) : 25);
        }

        // Photos verification (15 points)
        if ($this->photos_status === 'approved') {
            $score += 15;
        }

        // Details verification (15 points)
        if ($this->details_status === 'approved') {
            $score += 15;
        }

        // Legal compliance (15 points total)
        if ($this->has_business_license) {
            $score += 5;
        }
        if ($this->has_safety_certificate) {
            $score += 5;
        }
        if ($this->has_insurance && ! $this->isInsuranceExpired()) {
            $score += 5;
        }

        return min(100, (int) $score);
    }

    public function updateOverallStatus(): void
    {
        $score = $this->calculateVerificationScore();
        $this->verification_score = $score;

        if ($score >= 80 && $this->ownership_status === 'approved') {
            $this->overall_status = 'verified';
            $this->has_verified_badge = true;
            $this->last_verified_at = now();
            $this->next_verification_due = now()->addYear();
        } elseif ($score >= 50) {
            $this->overall_status = 'under_review';
            $this->has_verified_badge = false;
        } else {
            $this->overall_status = 'unverified';
            $this->has_verified_badge = false;
        }

        $this->save();
    }

    public function isVerified(): bool
    {
        return $this->overall_status === 'verified' && $this->has_verified_badge;
    }

    public function needsReverification(): bool
    {
        if (! $this->next_verification_due) {
            return false;
        }

        return Carbon::now()->gte($this->next_verification_due);
    }

    public function isInsuranceExpired(): bool
    {
        if (! $this->has_insurance || ! $this->insurance_expiry_date) {
            return true;
        }

        return Carbon::now()->gt($this->insurance_expiry_date);
    }

    public function canScheduleInspection(): bool
    {
        return in_array($this->inspection_status, ['not_required', 'pending', 'failed']);
    }

    public function approve(User $admin): void
    {
        $this->overall_status = 'verified';
        $this->has_verified_badge = true;
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        $this->last_verified_at = now();
        $this->next_verification_due = now()->addYear();
        $this->updateOverallStatus();
    }

    public function reject(User $admin, ?string $reason = null): void
    {
        $this->overall_status = 'rejected';
        $this->has_verified_badge = false;
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        if ($reason) {
            $this->admin_notes = $reason;
        }
        $this->save();
    }
}
