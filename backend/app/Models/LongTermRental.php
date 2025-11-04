<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class LongTermRental extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'property_id',
        'tenant_id',
        'owner_id',
        'start_date',
        'end_date',
        'duration_months',
        'rental_type',
        'monthly_rent',
        'security_deposit',
        'total_rent',
        'payment_frequency',
        'payment_day_of_month',
        'deposit_status',
        'deposit_paid_amount',
        'deposit_paid_at',
        'deposit_returned_amount',
        'deposit_returned_at',
        'lease_agreement_path',
        'lease_signed_at',
        'lease_auto_generated',
        'utilities_included',
        'utilities_paid_by_tenant',
        'utilities_estimate',
        'maintenance_included',
        'maintenance_terms',
        'status',
        'cancellation_reason',
        'cancelled_at',
        'auto_renewable',
        'renewal_notice_days',
        'renewal_requested_at',
        'renewal_status',
        'special_terms',
        'house_rules',
        'pets_allowed',
        'smoking_allowed',
        'move_in_inspection_at',
        'move_out_inspection_at',
        'move_in_condition_notes',
        'move_out_condition_notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'monthly_rent' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'total_rent' => 'decimal:2',
        'deposit_paid_amount' => 'decimal:2',
        'deposit_returned_amount' => 'decimal:2',
        'utilities_estimate' => 'decimal:2',
        'deposit_paid_at' => 'datetime',
        'deposit_returned_at' => 'datetime',
        'lease_signed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'renewal_requested_at' => 'datetime',
        'move_in_inspection_at' => 'datetime',
        'move_out_inspection_at' => 'datetime',
        'utilities_included' => 'array',
        'utilities_paid_by_tenant' => 'array',
        'house_rules' => 'array',
        'lease_auto_generated' => 'boolean',
        'maintenance_included' => 'boolean',
        'auto_renewable' => 'boolean',
        'pets_allowed' => 'boolean',
        'smoking_allowed' => 'boolean',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function rentPayments(): HasMany
    {
        return $this->hasMany(RentPayment::class);
    }

    public function maintenanceRequests(): HasMany
    {
        return $this->hasMany(MaintenanceRequest::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<=', Carbon::now()->addDays($days))
            ->where('end_date', '>=', Carbon::now());
    }

    // Helper Methods
    public function isActive(): bool
    {
        return $this->status === 'active' 
            && $this->start_date <= Carbon::now()
            && $this->end_date >= Carbon::now();
    }

    public function isExpired(): bool
    {
        return $this->end_date < Carbon::now();
    }

    public function daysUntilExpiry(): int
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    public function canRequestRenewal(): bool
    {
        if (!$this->auto_renewable) {
            return false;
        }
        
        $daysUntilExpiry = $this->daysUntilExpiry();
        return $daysUntilExpiry > 0 && $daysUntilExpiry <= $this->renewal_notice_days;
    }

    public function depositPaidInFull(): bool
    {
        return $this->deposit_status === 'paid' 
            && $this->deposit_paid_amount >= $this->security_deposit;
    }

    public function generatePaymentSchedule(): void
    {
        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);
        
        // Create deposit payment
        RentPayment::create([
            'long_term_rental_id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'payment_type' => 'deposit',
            'due_date' => $startDate->copy()->subDays(7), // Due 7 days before move-in
            'amount_due' => $this->security_deposit,
            'status' => 'scheduled',
        ]);
        
        // Create monthly rent payments
        $currentDate = $startDate->copy();
        $monthNumber = 1;
        
        while ($currentDate <= $endDate) {
            RentPayment::create([
                'long_term_rental_id' => $this->id,
                'tenant_id' => $this->tenant_id,
                'payment_type' => 'monthly_rent',
                'month_number' => $monthNumber,
                'due_date' => $currentDate->copy()->day($this->payment_day_of_month),
                'amount_due' => $this->monthly_rent,
                'status' => 'scheduled',
            ]);
            
            $currentDate->addMonth();
            $monthNumber++;
        }
    }
}
