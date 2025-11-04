<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MaintenanceRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'long_term_rental_id',
        'property_id',
        'tenant_id',
        'assigned_to',
        'service_provider_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'preferred_date',
        'scheduled_date',
        'completed_at',
        'requires_access',
        'access_instructions',
        'photos',
        'documents',
        'estimated_cost',
        'actual_cost',
        'payment_responsibility',
        'resolution_notes',
        'completion_photos',
        'tenant_rating',
        'tenant_feedback',
    ];

    protected $casts = [
        'preferred_date' => 'datetime',
        'scheduled_date' => 'datetime',
        'completed_at' => 'datetime',
        'requires_access' => 'boolean',
        'photos' => 'array',
        'documents' => 'array',
        'completion_photos' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
    ];

    // Relationships
    public function longTermRental(): BelongsTo
    {
        return $this->belongsTo(LongTermRental::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    // Scopes
    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['submitted', 'acknowledged', 'scheduled', 'in_progress']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }

    // Helper Methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }

    public function markAsCompleted(string $resolutionNotes, ?array $completionPhotos = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'resolution_notes' => $resolutionNotes,
            'completion_photos' => $completionPhotos,
        ]);
    }

    public function assign(int $userId): void
    {
        $this->update([
            'assigned_to' => $userId,
            'status' => 'acknowledged',
        ]);
    }
}
