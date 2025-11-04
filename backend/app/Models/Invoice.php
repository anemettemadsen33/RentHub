<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'booking_id',
        'user_id',
        'property_id',
        'bank_account_id',
        'invoice_date',
        'due_date',
        'status',
        'subtotal',
        'cleaning_fee',
        'security_deposit',
        'taxes',
        'total_amount',
        'currency',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'property_title',
        'property_address',
        'paid_at',
        'payment_method',
        'payment_reference',
        'pdf_path',
        'sent_at',
        'send_count',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'taxes' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Generate unique invoice number
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ym');
        $lastInvoice = static::whereRaw('invoice_number LIKE ?', [$date.'%'])
            ->orderBy('invoice_number', 'desc')
            ->first();

        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return $date.$newNumber;
    }

    // Check if invoice is overdue
    public function isOverdue(): bool
    {
        return $this->status !== 'paid'
            && $this->due_date
            && $this->due_date->isPast();
    }

    // Mark as paid
    public function markAsPaid(string $paymentMethod, ?string $reference = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $reference,
        ]);
    }

    // Get PDF URL
    public function getPdfUrlAttribute(): ?string
    {
        if ($this->pdf_path) {
            return Storage::url($this->pdf_path);
        }

        return null;
    }

    // Scope for overdue invoices
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
            ->where('due_date', '<', now());
    }

    // Scope for unpaid invoices
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['draft', 'sent', 'overdue']);
    }
}
