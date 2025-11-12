<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = ['code', 'symbol', 'name', 'decimal_places', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'decimal_places' => 'integer',
    ];

    public function exchangeRatesFrom()
    {
        return $this->hasMany(ExchangeRate::class, 'from_currency_id');
    }

    public function exchangeRatesTo()
    {
        return $this->hasMany(ExchangeRate::class, 'to_currency_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function getDefault()
    {
        // First try to get default from settings/config
        $defaultCode = config('app.currency', 'USD');
        
        $currency = static::where('code', $defaultCode)
            ->where('is_active', true)
            ->first();
            
        // If configured default doesn't exist, return first active currency
        if (!$currency) {
            $currency = static::active()->first();
        }
        
        return $currency;
    }
}
