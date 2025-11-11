<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class GuestReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_screening_id',
        'guest_verification_id',
        'user_id',
        'reference_name',
        'reference_email',
        'reference_phone',
        'reference_type',
        'relationship',
        'relationship_description',
        'status',
        'verification_notes',
        'verification_code',
        'verification_token',
        'responded',
        'responded_at',
        'rating',
        'comments',
        'would_rent_again',
        'reliable_tenant',
        'damages_caused',
        'payment_issues',
        'strengths',
        'concerns',
        'contact_attempts',
        'last_contact_at',
        'expires_at',
    ];

    protected $casts = [
        'responded' => 'boolean',
        'responded_at' => 'datetime',
        'rating' => 'integer',
        'would_rent_again' => 'boolean',
        'reliable_tenant' => 'boolean',
        'damages_caused' => 'boolean',
        'payment_issues' => 'boolean',
        'contact_attempts' => 'integer',
        'last_contact_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($reference) {
            // Ensure user_id is set based on linked verification/screening for NOT NULL constraint
            if (! $reference->user_id) {
                if ($reference->guest_verification_id && $reference->verification) {
                    $reference->user_id = $reference->verification->user_id;
                } elseif ($reference->guest_screening_id && $reference->screening) {
                    $reference->user_id = $reference->screening->user_id;
                }
            }
            if (! $reference->verification_code) {
                $reference->verification_code = Str::random(32);
            }
            if (! $reference->expires_at) {
                $reference->expires_at = now()->addDays(14);
            }
        });
    }

    public function screening(): BelongsTo
    {
        return $this->belongsTo(GuestScreening::class, 'guest_screening_id');
    }

    public function verification(): BelongsTo
    {
        return $this->belongsTo(GuestVerification::class, 'guest_verification_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sendVerificationRequest(): void
    {
        $this->contact_attempts++;
        $this->last_contact_at = now();
        $this->status = 'contacted';
        $this->save();
    }

    public function submitResponse(array $data): void
    {
        $this->fill($data);
        $this->responded = true;
        $this->responded_at = now();
        $this->status = 'verified';
        $this->save();

        // Update screening if related
        if ($this->screening) {
            $this->screening->increment('references_verified');
            $this->screening->screening_score = $this->screening->calculateScreeningScore();
            $this->screening->risk_level = $this->screening->determineRiskLevel();
            $this->screening->save();
        }

        // Update verification if related
        if ($this->verification) {
            $this->verification->increment('references_verified');
            $this->verification->updateTrustScore();
        }
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeResponded($query)
    {
        return $query->where('responded', true);
    }

    /**
     * Verify this reference with a rating and optional comments (used by public token endpoint)
     */
    public function verify(int $rating, ?string $comments = null): void
    {
        $this->rating = $rating;
        if ($comments !== null) {
            $this->comments = $comments;
        }
        $this->status = 'verified';
        $this->responded = true;
        $this->responded_at = now();
        $this->save();

        // Update linked verification metrics
        if ($this->verification) {
            $this->verification->increment('references_verified');
            $this->verification->updateTrustScore();
        }
        // Update linked screening metrics
        if ($this->screening) {
            $this->screening->increment('references_verified');
            $this->screening->screening_score = $this->screening->calculateScreeningScore();
            $this->screening->risk_level = $this->screening->determineRiskLevel();
            $this->screening->save();
        }
    }
}
