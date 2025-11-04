<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAuditLog extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'category',
        'event',
        'user_id',
        'successful',
        'metadata',
        'created_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
