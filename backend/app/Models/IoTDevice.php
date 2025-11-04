<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class IoTDevice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'iot_devices';

    protected $fillable = [
        'property_id',
        'iot_device_type_id',
        'device_name',
        'device_id',
        'manufacturer',
        'model',
        'location_in_property',
        'status',
        'current_state',
        'configuration',
        'last_communication',
        'guest_accessible',
        'is_active',
    ];

    protected $casts = [
        'current_state' => 'array',
        'configuration' => 'array',
        'last_communication' => 'datetime',
        'guest_accessible' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function deviceType(): BelongsTo
    {
        return $this->belongsTo(IoTDeviceType::class, 'iot_device_type_id');
    }

    public function commands(): HasMany
    {
        return $this->hasMany(IoTDeviceCommand::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(IoTDeviceLog::class);
    }

    public function isOnline(): bool
    {
        return $this->status === 'online';
    }

    public function updateState(array $state): void
    {
        $this->update([
            'current_state' => array_merge($this->current_state ?? [], $state),
            'last_communication' => now(),
        ]);

        $this->logs()->create([
            'event_type' => 'state_change',
            'event_data' => $state,
            'event_timestamp' => now(),
        ]);
    }
}
