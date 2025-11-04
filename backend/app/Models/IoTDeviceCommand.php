<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IoTDeviceCommand extends Model
{
    use HasFactory;

    protected $fillable = [
        'iot_device_id',
        'user_id',
        'command_type',
        'command_params',
        'status',
        'response',
        'sent_at',
        'executed_at',
    ];

    protected $casts = [
        'command_params' => 'array',
        'sent_at' => 'datetime',
        'executed_at' => 'datetime',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(IoTDevice::class, 'iot_device_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }

    public function markAsExecuted(?string $response = null): void
    {
        $this->update([
            'status' => 'executed',
            'response' => $response,
            'executed_at' => now(),
        ]);
    }

    public function markAsFailed(?string $response = null): void
    {
        $this->update([
            'status' => 'failed',
            'response' => $response,
        ]);
    }
}
