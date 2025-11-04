<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyComparison extends Model
{
    protected $fillable = [
        'user_id',
        'property_ids',
        'session_id',
        'expires_at',
    ];

    protected $casts = [
        'property_ids' => 'array',
        'expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function properties()
    {
        return Property::whereIn('id', $this->property_ids ?? [])->get();
    }

    public function addProperty(int $propertyId): bool
    {
        $propertyIds = $this->property_ids ?? [];
        
        if (count($propertyIds) >= 4) {
            return false; // Max 4 properties
        }
        
        if (!in_array($propertyId, $propertyIds)) {
            $propertyIds[] = $propertyId;
            $this->property_ids = $propertyIds;
            $this->save();
        }
        
        return true;
    }

    public function removeProperty(int $propertyId): void
    {
        $propertyIds = $this->property_ids ?? [];
        $propertyIds = array_values(array_filter($propertyIds, fn($id) => $id !== $propertyId));
        $this->property_ids = $propertyIds;
        $this->save();
    }
}
