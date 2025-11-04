<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CleaningSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'service_provider_id',
        'created_by',
        'schedule_type',
        'frequency',
        'days_of_week',
        'day_of_month',
        'custom_schedule',
        'preferred_time',
        'duration_hours',
        'service_type',
        'cleaning_checklist',
        'special_instructions',
        'start_date',
        'end_date',
        'active',
        'last_executed_at',
        'next_execution_at',
        'auto_book',
        'book_days_in_advance',
        'notify_provider',
        'notify_owner',
        'reminder_hours_before',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'custom_schedule' => 'array',
        'cleaning_checklist' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'active' => 'boolean',
        'last_executed_at' => 'datetime',
        'next_execution_at' => 'datetime',
        'auto_book' => 'boolean',
        'notify_provider' => 'boolean',
        'notify_owner' => 'boolean',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeDueForExecution($query)
    {
        return $query->active()
            ->where('next_execution_at', '<=', now())
            ->whereNull('end_date')
            ->orWhere('end_date', '>=', now());
    }

    // Helper Methods
    public function calculateNextExecution(): ?Carbon
    {
        if (! $this->active) {
            return null;
        }

        $baseDate = $this->last_executed_at ? $this->last_executed_at->copy() : Carbon::parse($this->start_date);

        switch ($this->frequency) {
            case 'daily':
                return $baseDate->addDay();

            case 'weekly':
                return $baseDate->addWeek();

            case 'biweekly':
                return $baseDate->addWeeks(2);

            case 'monthly':
                $nextMonth = $baseDate->addMonth();
                if ($this->day_of_month) {
                    $nextMonth->day($this->day_of_month);
                }

                return $nextMonth;

            case 'custom':
                if ($this->days_of_week && ! empty($this->days_of_week)) {
                    // Find next day in the week list
                    $today = $baseDate->dayOfWeek;
                    $daysOfWeek = $this->days_of_week;
                    sort($daysOfWeek);

                    foreach ($daysOfWeek as $day) {
                        if ($day > $today) {
                            return $baseDate->next($day);
                        }
                    }

                    // If no day found this week, go to first day of next week
                    return $baseDate->next($daysOfWeek[0]);
                }
                break;
        }

        return null;
    }

    public function updateNextExecution(): void
    {
        $next = $this->calculateNextExecution();

        if ($next && $this->end_date && $next->gt($this->end_date)) {
            $this->active = false;
            $next = null;
        }

        $this->update([
            'next_execution_at' => $next,
            'active' => $this->active,
        ]);
    }

    public function execute(): ?CleaningService
    {
        if (! $this->auto_book) {
            return null;
        }

        // Create a new cleaning service
        $cleaningService = CleaningService::create([
            'property_id' => $this->property_id,
            'service_provider_id' => $this->service_provider_id,
            'requested_by' => $this->created_by,
            'service_type' => $this->service_type,
            'checklist' => $this->cleaning_checklist,
            'special_instructions' => $this->special_instructions,
            'scheduled_date' => $this->next_execution_at,
            'scheduled_time' => $this->preferred_time,
            'estimated_duration_hours' => $this->duration_hours,
            'status' => $this->service_provider_id ? 'confirmed' : 'scheduled',
        ]);

        // Update schedule
        $this->update([
            'last_executed_at' => now(),
        ]);

        $this->updateNextExecution();

        return $cleaningService;
    }

    public function deactivate(): void
    {
        $this->update([
            'active' => false,
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'active' => true,
        ]);
        $this->updateNextExecution();
    }
}
