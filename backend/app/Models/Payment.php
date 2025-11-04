<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'booking_id',
        'invoice_id',
        'user_id',
        'amount',
        'currency',
        'type',
        'status',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'gateway_reference',
        'bank_reference',
        'bank_receipt',
        'initiated_at',
        'completed_at',
        'failed_at',
        'refunded_at',
        'failure_reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Generate unique payment number
    public static function generatePaymentNumber(): string
    {
        $date = now()->format('Ym');
        $lastPayment = static::whereRaw('payment_number LIKE ?', [$date . '%'])
            ->orderBy('payment_number', 'desc')
            ->first();

        if ($lastPayment) {
            $lastNumber = (int) substr($lastPayment->payment_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'PAY' . $date . $newNumber;
    }

    // Mark as completed
    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
        ], $data));

        // Update related invoice
        if ($this->invoice) {
            $this->invoice->markAsPaid($this->payment_method, $this->transaction_id);
        }

        // Update booking payment status
        if ($this->booking) {
            $this->booking->update(['payment_status' => 'paid']);
        }
    }

    // Mark as failed
    public function markAsFailed(string $reason): void
    {
        $this->update([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_reason' => $reason,
        ]);
    }

    // Mark as refunded
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
            'refunded_at' => now(),
        ]);

        // Update booking payment status
        if ($this->booking) {
            $this->booking->update(['payment_status' => 'refunded']);
        }
    }

    // Scope for completed payments
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope for pending payments
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
