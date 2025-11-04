<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SimilarProperty extends Model
{
    protected $fillable = [
        'property_id',
        'similar_property_id',
        'similarity_score',
        'similarity_factors',
        'calculated_at',
    ];

    protected $casts = [
        'similarity_factors' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id');
    }

    public function similarProperty(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'similar_property_id');
    }
}
