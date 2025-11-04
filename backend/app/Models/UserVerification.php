<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'id_verification_status',
        'id_document_type',
        'id_document_number',
        'id_front_image',
        'id_back_image',
        'selfie_image',
        'id_verified_at',
        'id_rejection_reason',
        'phone_verification_status',
        'phone_number',
        'phone_verification_code',
        'phone_verified_at',
        'phone_verification_code_sent_at',
        'email_verification_status',
        'email_verified_at',
        'address_verification_status',
        'address',
        'address_proof_document',
        'address_proof_image',
        'address_verified_at',
        'address_rejection_reason',
        'background_check_status',
        'background_check_provider',
        'background_check_reference',
        'background_check_result',
        'background_check_completed_at',
        'overall_status',
        'verification_score',
        'reviewed_by',
        'admin_notes',
    ];

    protected $casts = [
        'id_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'phone_verification_code_sent_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'address_verified_at' => 'datetime',
        'background_check_result' => 'array',
        'background_check_completed_at' => 'datetime',
        'verification_score' => 'integer',
    ];

    protected $hidden = [
        'phone_verification_code',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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

        if ($this->id_verification_status === 'approved') {
            $score += 30;
        }
        if ($this->phone_verification_status === 'verified') {
            $score += 20;
        }
        if ($this->email_verification_status === 'verified') {
            $score += 20;
        }
        if ($this->address_verification_status === 'approved') {
            $score += 20;
        }
        if ($this->background_check_status === 'completed') {
            $score += 10;
        }

        return $score;
    }

    public function updateOverallStatus(): void
    {
        $score = $this->calculateVerificationScore();
        $this->verification_score = $score;

        if ($score === 0) {
            $this->overall_status = 'unverified';
        } elseif ($score < 70) {
            $this->overall_status = 'partially_verified';
        } else {
            $this->overall_status = 'fully_verified';
        }

        $this->save();
    }

    public function isFullyVerified(): bool
    {
        return $this->overall_status === 'fully_verified';
    }

    public function canRequestBackgroundCheck(): bool
    {
        return $this->id_verification_status === 'approved'
            && $this->background_check_status === 'not_requested';
    }
}
