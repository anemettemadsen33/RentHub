<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataDeletionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'requested_at',
        'scheduled_for',
        'completed_at',
        'status',
        'categories',
        'reason',
    ];

    protected $casts = [
        'categories' => 'array',
        'requested_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
