<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IoTDeviceType extends Model
{
    use HasFactory;

    protected $table = 'iot_device_types';

    protected $fillable = [
        'name',
        'slug',
        'capabilities',
        'default_config',
    ];

    protected $casts = [
        'capabilities' => 'array',
        'default_config' => 'array',
    ];

    public function devices(): HasMany
    {
        return $this->hasMany(IoTDevice::class);
    }
}
