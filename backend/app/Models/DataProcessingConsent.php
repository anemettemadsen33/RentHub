<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DataProcessingConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_type',
        'categories',
        'purpose',
        'do_not_sell',
        'consented_at',
        'ip_address',
        'user_agent',
        'consent_text',
    ];

    protected $casts = [
        'categories' => 'array',
        'do_not_sell' => 'boolean',
        'consented_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
