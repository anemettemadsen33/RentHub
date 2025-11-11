<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'payload',
        'event_timestamp',
        'user_id',
        'user_role',
        'client_id',
    ];

    protected $casts = [
        'payload' => 'array',
        'event_timestamp' => 'datetime',
    ];
}
