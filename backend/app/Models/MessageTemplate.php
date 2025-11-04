<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'category',
        'trigger_event',
        'subject',
        'content',
        'variables',
        'is_active',
        'is_default',
        'usage_count',
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByTrigger($query, string $trigger)
    {
        return $query->where('trigger_event', $trigger);
    }

    public function render(array $data = []): string
    {
        $content = $this->content;

        foreach ($data as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
        }

        return $content;
    }

    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }
}
