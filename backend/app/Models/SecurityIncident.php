<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityIncident extends Model
{
    protected $fillable = [
        'type',
        'severity',
        'status',
        'user_id',
        'ip_address',
        'user_agent',
        'url',
        'method',
        'details',
        'detected_at',
        'escalated_at',
        'resolved_at',
        'resolved_by',
        'resolution_notes',
    ];

    protected $casts = [
        'details' => 'array',
        'detected_at' => 'datetime',
        'escalated_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isCritical(): bool
    {
        return $this->severity === 'critical';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'investigating', 'escalated']);
    }

    public function resolve(int $resolvedBy, string $notes): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
            'resolution_notes' => $notes,
        ]);
    }

    public function escalate(): void
    {
        $this->update([
            'status' => 'escalated',
            'escalated_at' => now(),
        ]);
    }
}
