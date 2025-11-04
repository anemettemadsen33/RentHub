<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoResponse extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'trigger_type',
        'trigger_conditions',
        'response_content',
        'template_id',
        'is_active',
        'priority',
        'usage_count',
        'active_from',
        'active_until',
        'settings',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'active_from' => 'datetime',
        'active_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(MessageTemplate::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('active_from')
                    ->orWhere('active_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('active_until')
                    ->orWhere('active_until', '>=', now());
            });
    }

    public function scopeByTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    public function matches(string $message): bool
    {
        if ($this->trigger_type !== 'keyword') {
            return false;
        }

        $keywords = $this->trigger_conditions['keywords'] ?? [];
        $message = strtolower($message);

        foreach ($keywords as $keyword) {
            if (str_contains($message, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    public function isCurrentlyActive(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->active_from && $this->active_from->isFuture()) {
            return false;
        }

        if ($this->active_until && $this->active_until->isPast()) {
            return false;
        }

        return true;
    }
}
