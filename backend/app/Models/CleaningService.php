<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CleaningService extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'booking_id',
        'long_term_rental_id',
        'service_provider_id',
        'requested_by',
        'service_type',
        'description',
        'checklist',
        'special_instructions',
        'scheduled_date',
        'scheduled_time',
        'estimated_duration_hours',
        'started_at',
        'completed_at',
        'requires_key',
        'access_instructions',
        'access_code',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'completed_checklist',
        'before_photos',
        'after_photos',
        'completion_notes',
        'issues_found',
        'rating',
        'feedback',
        'rated_at',
        'estimated_cost',
        'actual_cost',
        'payment_status',
        'paid_at',
        'provider_brings_supplies',
        'supplies_needed',
    ];

    protected $casts = [
        'checklist' => 'array',
        'scheduled_date' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_checklist' => 'array',
        'before_photos' => 'array',
        'after_photos' => 'array',
        'issues_found' => 'array',
        'rated_at' => 'datetime',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'paid_at' => 'datetime',
        'requires_key' => 'boolean',
        'provider_brings_supplies' => 'boolean',
        'supplies_needed' => 'array',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function longTermRental(): BelongsTo
    {
        return $this->belongsTo(LongTermRental::class);
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed'])
            ->where('scheduled_date', '>=', now());
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['scheduled', 'confirmed']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', today());
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canCancel(): bool
    {
        return in_array($this->status, ['scheduled', 'confirmed']);
    }

    public function canRate(): bool
    {
        return $this->status === 'completed' && !$this->rating;
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(array $data): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_checklist' => $data['completed_checklist'] ?? null,
            'after_photos' => $data['after_photos'] ?? null,
            'completion_notes' => $data['completion_notes'] ?? null,
            'issues_found' => $data['issues_found'] ?? null,
            'actual_cost' => $data['actual_cost'] ?? $this->estimated_cost,
        ]);

        // Update provider stats
        if ($this->serviceProvider) {
            $this->serviceProvider->markJobCompleted();
        }
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        // Update provider stats
        if ($this->serviceProvider) {
            $this->serviceProvider->markJobCancelled();
        }
    }

    public function rate(int $rating, ?string $feedback = null): void
    {
        $this->update([
            'rating' => $rating,
            'feedback' => $feedback,
            'rated_at' => now(),
        ]);

        // Update provider rating
        if ($this->serviceProvider) {
            $this->serviceProvider->updateRating($rating);
        }
    }

    public function assignProvider(int $serviceProviderId): void
    {
        $this->update([
            'service_provider_id' => $serviceProviderId,
            'status' => 'confirmed',
        ]);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'scheduled' => 'warning',
            'confirmed' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            'needs_rescheduling' => 'warning',
            default => 'secondary',
        };
    }
}
