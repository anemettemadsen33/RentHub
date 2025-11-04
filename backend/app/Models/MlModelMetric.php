<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlModelMetric extends Model
{
    protected $fillable = [
        'model_name',
        'model_version',
        'metric_name',
        'metric_value',
        'metadata',
        'measured_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'measured_at' => 'datetime',
    ];

    public static function record(
        string $modelName,
        string $modelVersion,
        string $metricName,
        float $metricValue,
        ?array $metadata = null
    ): void {
        self::create([
            'model_name' => $modelName,
            'model_version' => $modelVersion,
            'metric_name' => $metricName,
            'metric_value' => $metricValue,
            'metadata' => $metadata,
            'measured_at' => now(),
        ]);
    }
}
