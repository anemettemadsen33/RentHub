<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WishlistItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'wishlist_id',
        'property_id',
        'notes',
        'price_alert',
        'notify_availability',
    ];

    protected $casts = [
        'price_alert' => 'decimal:2',
        'notify_availability' => 'boolean',
    ];

    public function wishlist(): BelongsTo
    {
        return $this->belongsTo(Wishlist::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function shouldNotifyPriceDrop(float $newPrice): bool
    {
        return $this->price_alert && $newPrice <= $this->price_alert;
    }
}
