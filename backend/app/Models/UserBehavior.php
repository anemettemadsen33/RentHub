<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBehavior extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'property_id',
        'metadata',
        'action_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'action_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public static function track(int $userId, string $action, ?int $propertyId = null, ?array $metadata = null): void
    {
        self::create([
            'user_id' => $userId,
            'action' => $action,
            'property_id' => $propertyId,
            'metadata' => $metadata,
            'action_at' => now(),
        ]);
    }
}
