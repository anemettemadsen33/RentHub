<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payout extends Model
{
    protected $fillable = [
        'payout_number',
        'user_id',
        'booking_id',
        'bank_account_id',
        'booking_amount',
        'commission_rate',
        'commission_amount',
        'payout_amount',
        'currency',
        'status',
        'payout_date',
        'completed_at',
        'failed_at',
        'payment_method',
        'transaction_reference',
        'period_start',
        'period_end',
        'failure_reason',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'booking_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'payout_amount' => 'decimal:2',
        'payout_date' => 'date',
        'completed_at' => 'datetime',
        'failed_at' => 'datetime',
        'period_start' => 'date',
        'period_end' => 'date',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    // Generate unique payout number
    public static function generatePayoutNumber(): string
    {
        $date = now()->format('Ym');
        $lastPayout = static::whereRaw('payout_number LIKE ?', [$date . '%'])
            ->orderBy('payout_number', 'desc')
            ->first();

        if ($lastPayout) {
            $lastNumber = (int) substr($lastPayout->payout_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'PO' . $date . $newNumber;
    }

    // Calculate commission and payout amounts
    public static function calculateAmounts(float $bookingAmount, float $commissionRate): array
    {
        $commissionAmount = round($bookingAmount * ($commissionRate / 100), 2);
        $payoutAmount = round($bookingAmount - $commissionAmount, 2);

        return [
            'commission_amount' => $commissionAmount,
            'payout_amount' => $payoutAmount,
        ];
    }

    // Mark as completed
    public function markAsCompleted(array $data = []): void
    {
        $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
        ], $data));
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

    // Scope for pending payouts
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // Scope for due payouts (ready to be paid)
    public function scopeDue($query)
    {
        return $query->where('status', 'pending')
            ->where('payout_date', '<=', now());
    }
}
