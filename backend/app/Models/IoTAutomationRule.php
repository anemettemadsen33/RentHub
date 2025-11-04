<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IoTAutomationRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'rule_name',
        'description',
        'trigger_conditions',
        'actions',
        'is_active',
        'last_executed',
        'execution_count',
    ];

    protected $casts = [
        'trigger_conditions' => 'array',
        'actions' => 'array',
        'is_active' => 'boolean',
        'last_executed' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function execute(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed' => now()]);
    }
}
