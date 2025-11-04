<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevenueSuggestion extends Model
{
    protected $fillable = [
        'property_id',
        'suggestion_type',
        'description',
        'parameters',
        'expected_impact',
        'confidence',
        'applied',
        'applied_at',
        'actual_impact',
        'valid_until',
    ];

    protected $casts = [
        'parameters' => 'array',
        'applied' => 'boolean',
        'applied_at' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function apply(): void
    {
        $this->update([
            'applied' => true,
            'applied_at' => now(),
        ]);
    }

    public function recordImpact(float $actualImpact): void
    {
        $this->update(['actual_impact' => $actualImpact]);
    }
}
