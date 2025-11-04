<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'locale',
        'group',
        'key',
        'value',
    ];

    protected static function booted()
    {
        static::saved(function () {
            Cache::forget('translations');
        });

        static::deleted(function () {
            Cache::forget('translations');
        });
    }

    public static function get(string $key, ?string $locale = null, string $group = 'common', $default = null)
    {
        $locale = $locale ?? config('app.locale', 'en');

        $translation = Cache::remember(
            "translation.{$locale}.{$group}.{$key}",
            3600,
            fn () => self::where('locale', $locale)
                ->where('group', $group)
                ->where('key', $key)
                ->first()
        );

        return $translation?->value ?? $default ?? $key;
    }

    public static function getAllByLocale(string $locale, ?string $group = null)
    {
        $query = self::where('locale', $locale);

        if ($group) {
            $query->where('group', $group);
        }

        return $query->get()->pluck('value', 'key')->toArray();
    }
}
