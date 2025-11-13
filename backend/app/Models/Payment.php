<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

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
        'paid_at',
        'failed_at',
        'refunded_at',
        'refunded_amount',
        'failure_reason',
        'notes',
        'metadata',
        // Bank transfer fields
        'bank_name',
        'account_holder',
        'account_number',
        'transfer_reference',
        'transfer_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
        'failed_at' => 'datetime',
        'transfer_date' => 'datetime',
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

    public function proofs()
    {
        return $this->hasMany(PaymentProof::class);
    }

    // Generate unique payment number
    public static function generatePaymentNumber(): string
    {
        $prefix = 'PAY'.now()->format('Ym');
        do {
            $suffix = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $number = $prefix.$suffix;
        } while (static::where('payment_number', $number)->exists());

        return $number;
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
