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
        'user_id',
        'reference_name',
        'reference_email',
        'reference_phone',
        'relationship',
        'relationship_description',
        'status',
        'verification_notes',
        'verification_code',
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

        $this->screening->increment('references_verified');
        $this->screening->screening_score = $this->screening->calculateScreeningScore();
        $this->screening->risk_level = $this->screening->determineRiskLevel();
        $this->screening->save();
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
}
