<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedSearchMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'saved_search_id',
        'property_id',
        'notified',
        'notified_at',
    ];

    protected $casts = [
        'notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    /**
     * Get the saved search
     */
    public function savedSearch(): BelongsTo
    {
        return $this->belongsTo(SavedSearch::class);
    }

    /**
     * Get the property
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
