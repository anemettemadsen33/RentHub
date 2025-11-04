<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Wishlist extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'is_public',
        'share_token',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($wishlist) {
            if (! $wishlist->share_token) {
                $wishlist->share_token = Str::random(32);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'wishlist_items')
            ->withTimestamps()
            ->withPivot(['notes', 'price_alert', 'notify_availability']);
    }

    public function generateShareUrl(): string
    {
        return url("/wishlists/shared/{$this->share_token}");
    }
}
