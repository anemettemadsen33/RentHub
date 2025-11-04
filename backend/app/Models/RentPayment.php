<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentPayment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'long_term_rental_id',
        'tenant_id',
        'invoice_id',
        'payment_type',
        'month_number',
        'due_date',
        'payment_date',
        'amount_due',
        'amount_paid',
        'late_fee',
        'discount',
        'status',
        'days_overdue',
        'payment_method',
        'transaction_id',
        'reminder_sent_at',
        'reminder_count',
        'notes',
    ];

    protected $casts = [
        'due_date' => 'date',
        'payment_date' => 'date',
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'reminder_sent_at' => 'datetime',
    ];

    // Relationships
    public function longTermRental(): BelongsTo
    {
        return $this->belongsTo(LongTermRental::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', Carbon::now());
    }

    public function scopeUpcoming($query, $days = 7)
    {
        return $query->where('status', 'scheduled')
            ->where('due_date', '<=', Carbon::now()->addDays($days))
            ->where('due_date', '>=', Carbon::now());
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['scheduled', 'pending', 'processing']);
    }

    // Helper Methods
    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_date < Carbon::now();
    }

    public function calculateDaysOverdue(): int
    {
        if (! $this->isOverdue()) {
            return 0;
        }

        return Carbon::now()->diffInDays($this->due_date);
    }

    public function calculateLateFee(float $dailyRate = 5.0, float $maxFee = 100.0): float
    {
        $daysOverdue = $this->calculateDaysOverdue();

        if ($daysOverdue <= 0) {
            return 0;
        }

        $lateFee = $daysOverdue * $dailyRate;

        return min($lateFee, $maxFee);
    }

    public function getTotalAmount(): float
    {
        return $this->amount_due + $this->late_fee - $this->discount;
    }

    public function markAsPaid(float $amount, string $method, ?string $transactionId = null): void
    {
        $this->update([
            'status' => 'paid',
            'amount_paid' => $amount,
            'payment_date' => Carbon::now(),
            'payment_method' => $method,
            'transaction_id' => $transactionId,
        ]);
    }

    public function updateOverdueStatus(): void
    {
        if ($this->isOverdue()) {
            $daysOverdue = $this->calculateDaysOverdue();
            $lateFee = $this->calculateLateFee();

            $this->update([
                'status' => 'overdue',
                'days_overdue' => $daysOverdue,
                'late_fee' => $lateFee,
            ]);
        }
    }
}
