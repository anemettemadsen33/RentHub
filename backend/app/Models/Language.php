<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    protected $fillable = [
        'code',
        'name',
        'native_name',
        'flag_emoji',
        'is_rtl',
        'is_active',
        'is_default',
        'sort_order',
    ];

    protected $casts = [
        'is_rtl' => 'boolean',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    public static function getDefault(): ?self
    {
        return self::where('is_default', true)->first();
    }

    public static function getActive(): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('is_active', true)->orderBy('sort_order')->get();
    }
}
