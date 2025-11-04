<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FraudAlert extends Model
{
    protected $fillable = [
        'alert_type',
        'severity',
        'user_id',
        'property_id',
        'booking_id',
        'payment_id',
        'description',
        'evidence',
        'fraud_score',
        'status',
        'reviewed_by',
        'reviewed_at',
        'resolution_notes',
        'action_taken',
        'action_type',
    ];

    protected $casts = [
        'evidence' => 'array',
        'reviewed_at' => 'datetime',
        'action_taken' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function resolve(int $reviewedBy, string $resolutionNotes, ?string $actionType = null): void
    {
        $this->update([
            'status' => 'resolved',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'resolution_notes' => $resolutionNotes,
            'action_taken' => $actionType !== null,
            'action_type' => $actionType,
        ]);
    }

    public function markFalsePositive(int $reviewedBy, string $notes): void
    {
        $this->update([
            'status' => 'false_positive',
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
