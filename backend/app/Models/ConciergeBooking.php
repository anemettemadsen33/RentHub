<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ConciergeBooking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'property_id',
        'booking_id',
        'concierge_service_id',
        'booking_reference',
        'service_date',
        'service_time',
        'guests_count',
        'special_requests',
        'base_price',
        'extras_price',
        'total_price',
        'currency',
        'status',
        'payment_status',
        'payment_id',
        'confirmed_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
        'rating',
        'review',
        'reviewed_at',
        'contact_details',
    ];

    protected $casts = [
        'service_date' => 'datetime',
        'service_time' => 'datetime',
        'contact_details' => 'array',
        'base_price' => 'decimal:2',
        'extras_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'guests_count' => 'integer',
        'rating' => 'integer',
        'confirmed_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (! $booking->booking_reference) {
                $booking->booking_reference = 'CONC-'.strtoupper(Str::random(10));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function conciergeService(): BelongsTo
    {
        return $this->belongsTo(ConciergeService::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function confirm()
    {
        $this->status = 'confirmed';
        $this->confirmed_at = now();
        $this->save();
    }

    public function start()
    {
        $this->status = 'in_progress';
        $this->started_at = now();
        $this->save();
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->save();
    }

    public function cancel(?string $reason = null)
    {
        $this->status = 'cancelled';
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        $this->save();
    }

    public function addReview(int $rating, ?string $review = null)
    {
        $this->rating = $rating;
        $this->review = $review;
        $this->reviewed_at = now();
        $this->save();
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->whereIn('status', ['pending', 'confirmed'])
            ->where('service_date', '>=', now());
    }
}
