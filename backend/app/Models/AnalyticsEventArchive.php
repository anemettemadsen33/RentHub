<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEventArchive extends Model
{
    use HasFactory;

    protected $fillable = [
        'day', 'type', 'count',
    ];

    protected $casts = [
        'day' => 'date',
    ];
}
