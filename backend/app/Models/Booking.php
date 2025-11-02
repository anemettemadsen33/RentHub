<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class Booking extends Model
{
    protected $fillable = [
        'property_id',
        'user_id',
        'check_in',
        'check_out',
        'guests',
        'nights',
        'price_per_night',
        'subtotal',
        'cleaning_fee',
        'security_deposit',
        'taxes',
        'total_amount',
        'status',
        'guest_name',
        'guest_email',
        'guest_phone',
        'special_requests',
        'payment_status',
        'payment_method',
        'payment_transaction_id',
        'paid_at',
        'confirmed_at',
        'cancelled_at'
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
        'price_per_night' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'cleaning_fee' => 'decimal:2',
        'security_deposit' => 'decimal:2',
        'taxes' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    // Relationships
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function guest(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // Accessors
    public function getDaysUntilCheckInAttribute()
    {
        return Carbon::now()->diffInDays($this->check_in, false);
    }

    public function getDaysUntilCheckOutAttribute()
    {
        return Carbon::now()->diffInDays($this->check_out, false);
    }

    public function getIsActiveAttribute()
    {
        return in_array($this->status, ['confirmed', 'checked_in']);
    }

    public function getCanCancelAttribute()
    {
        return $this->status === 'pending' || 
               ($this->status === 'confirmed' && $this->getDaysUntilCheckInAttribute() > 1);
    }

    public function getCanCheckInAttribute()
    {
        return $this->status === 'confirmed' && 
               Carbon::now()->isSameDay($this->check_in) || 
               Carbon::now()->isAfter($this->check_in);
    }

    public function getCanCheckOutAttribute()
    {
        return $this->status === 'checked_in' && 
               Carbon::now()->isSameDay($this->check_out) || 
               Carbon::now()->isAfter($this->check_out);
    }

    public function getCanReviewAttribute()
    {
        return $this->status === 'completed' && !$this->review;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['confirmed', 'checked_in']);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('check_in', '>', Carbon::now())
                    ->whereIn('status', ['confirmed']);
    }

    public function scopeCurrent($query)
    {
        return $query->where('check_in', '<=', Carbon::now())
                    ->where('check_out', '>', Carbon::now())
                    ->where('status', 'checked_in');
    }

    public function scopePast($query)
    {
        return $query->where('check_out', '<', Carbon::now())
                    ->whereIn('status', ['checked_out', 'completed']);
    }

    // Methods
    public function calculateNights()
    {
        return Carbon::parse($this->check_in)->diffInDays(Carbon::parse($this->check_out));
    }

    public function calculateSubtotal()
    {
        return $this->nights * $this->price_per_night;
    }

    public function calculateTotal()
    {
        return $this->subtotal + $this->cleaning_fee + $this->security_deposit + $this->taxes;
    }
}
