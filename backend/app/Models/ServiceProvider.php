<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ServiceProvider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'company_name',
        'type',
        'email',
        'phone',
        'secondary_phone',
        'address',
        'city',
        'state',
        'zip_code',
        'business_license',
        'insurance_policy',
        'insurance_expiry',
        'certifications',
        'service_areas',
        'services_offered',
        'maintenance_specialties',
        'hourly_rate',
        'base_rate',
        'pricing_type',
        'working_hours',
        'holidays',
        'emergency_available',
        'average_rating',
        'total_jobs',
        'completed_jobs',
        'cancelled_jobs',
        'response_time_hours',
        'status',
        'verified',
        'verified_at',
        'documents',
        'photos',
        'bio',
        'notes',
    ];

    protected $casts = [
        'insurance_expiry' => 'date',
        'certifications' => 'array',
        'service_areas' => 'array',
        'services_offered' => 'array',
        'maintenance_specialties' => 'array',
        'hourly_rate' => 'decimal:2',
        'base_rate' => 'decimal:2',
        'working_hours' => 'array',
        'holidays' => 'array',
        'emergency_available' => 'boolean',
        'average_rating' => 'decimal:2',
        'response_time_hours' => 'decimal:2',
        'verified' => 'boolean',
        'verified_at' => 'datetime',
        'documents' => 'array',
        'photos' => 'array',
    ];

    // Relationships
    public function cleaningServices(): HasMany
    {
        return $this->hasMany(CleaningService::class);
    }

    public function cleaningSchedules(): HasMany
    {
        return $this->hasMany(CleaningSchedule::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class, 'assigned_to');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVerified($query)
    {
        return $query->where('verified', true);
    }

    public function scopeCleaning($query)
    {
        return $query->whereIn('type', ['cleaning', 'both']);
    }

    public function scopeMaintenance($query)
    {
        return $query->whereIn('type', ['maintenance', 'both']);
    }

    public function scopeTopRated($query, $minRating = 4.0)
    {
        return $query->where('average_rating', '>=', $minRating);
    }

    // Helper Methods
    public function isAvailable(string $date, string $time): bool
    {
        // Check if provider works on this day
        $dayOfWeek = strtolower(date('l', strtotime($date)));
        $workingHours = $this->working_hours[$dayOfWeek] ?? null;

        if (! $workingHours) {
            return false;
        }

        // Check if time is within working hours
        $checkTime = strtotime($time);
        $startTime = strtotime($workingHours['start']);
        $endTime = strtotime($workingHours['end']);

        if ($checkTime < $startTime || $checkTime > $endTime) {
            return false;
        }

        // Check if it's a holiday
        if ($this->holidays && in_array($date, $this->holidays)) {
            return false;
        }

        return true;
    }

    public function updateRating(int $newRating): void
    {
        $totalRating = $this->average_rating * $this->total_jobs;
        $this->total_jobs++;
        $this->average_rating = ($totalRating + $newRating) / $this->total_jobs;
        $this->save();
    }

    public function markJobCompleted(): void
    {
        $this->increment('completed_jobs');
    }

    public function markJobCancelled(): void
    {
        $this->increment('cancelled_jobs');
    }

    public function canServiceArea(string $city): bool
    {
        if (! $this->service_areas) {
            return true; // Services all areas if not specified
        }

        return in_array(strtolower($city), array_map('strtolower', $this->service_areas));
    }
}
