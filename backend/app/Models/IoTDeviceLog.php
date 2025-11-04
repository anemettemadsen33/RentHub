<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IoTDeviceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'iot_device_id',
        'event_type',
        'event_data',
        'description',
        'event_timestamp',
    ];

    protected $casts = [
        'event_data' => 'array',
        'event_timestamp' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'iot_device_id');
    }
}
