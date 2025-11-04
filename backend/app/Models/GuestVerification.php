<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GuestVerification extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'identity_status',
        'document_type',
        'document_number',
        'document_front',
        'document_back',
        'selfie_photo',
        'document_expiry_date',
        'identity_verified_at',
        'identity_rejection_reason',
        'credit_check_enabled',
        'credit_status',
        'credit_score',
        'credit_report',
        'credit_checked_at',
        'background_status',
        'background_notes',
        'background_checked_at',
        'references',
        'references_verified',
        'trust_score',
        'completed_bookings',
        'cancelled_bookings',
        'positive_reviews',
        'negative_reviews',
        'verified_by',
        'admin_notes',
    ];

    protected $casts = [
        'references' => 'array',
        'document_expiry_date' => 'date',
        'identity_verified_at' => 'datetime',
        'credit_checked_at' => 'datetime',
        'background_checked_at' => 'datetime',
        'trust_score' => 'decimal:2',
        'credit_check_enabled' => 'boolean',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function guestReferences(): HasMany
    {
        return $this->hasMany(GuestReference::class);
    }

    public function verificationLogs(): HasMany
    {
        return $this->hasMany(VerificationLog::class);
    }

    // Helper Methods
    public function isFullyVerified(): bool
    {
        return $this->identity_status === 'verified' &&
               $this->background_status === 'clear' &&
               (!$this->credit_check_enabled || $this->credit_status === 'approved');
    }

    public function canBook(): bool
    {
        return $this->isFullyVerified() || 
               ($this->identity_status === 'verified' && $this->trust_score >= 3.0);
    }

    public function calculateTrustScore(): float
    {
        $score = 3.0; // Base score
        
        // Identity verified
        if ($this->identity_status === 'verified') {
            $score += 0.5;
        }
        
        // Background clear
        if ($this->background_status === 'clear') {
            $score += 0.5;
        }
        
        // Credit approved
        if ($this->credit_status === 'approved') {
            $score += 0.3;
        }
        
        // Completed bookings
        $score += min($this->completed_bookings * 0.1, 1.0);
        
        // Positive reviews
        if ($this->completed_bookings > 0) {
            $reviewRatio = $this->positive_reviews / ($this->positive_reviews + $this->negative_reviews + 1);
            $score += $reviewRatio * 0.7;
        }
        
        // References verified
        $score += min($this->references_verified * 0.15, 0.5);
        
        // Penalties
        $score -= $this->cancelled_bookings * 0.2;
        $score -= $this->negative_reviews * 0.3;
        
        return max(0, min(5.0, round($score, 2)));
    }

    public function updateTrustScore(): void
    {
        $this->trust_score = $this->calculateTrustScore();
        $this->save();
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('identity_status', 'verified');
    }

    public function scopeFullyVerified($query)
    {
        return $query->where('identity_status', 'verified')
                     ->where('background_status', 'clear');
    }

    public function scopeHighTrust($query)
    {
        return $query->where('trust_score', '>=', 4.0);
    }
}
