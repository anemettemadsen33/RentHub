<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'file_path',
        'file_type',
        'file_size',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_size' => 'integer',
    ];

    /**
     * Get the payment that owns the proof.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the user who verified the proof.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Check if proof is pending verification.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if proof is verified.
     */
    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    /**
     * Check if proof is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Mark proof as verified.
     */
    public function markAsVerified(int $verifiedBy): void
    {
        $this->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => $verifiedBy,
            'rejection_reason' => null,
        ]);
    }

    /**
     * Mark proof as rejected.
     */
    public function markAsRejected(int $verifiedBy, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'verified_by' => $verifiedBy,
            'rejection_reason' => $reason,
            'verified_at' => null,
        ]);
    }
}
