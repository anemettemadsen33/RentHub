<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AccessCode extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'smart_lock_id',
        'booking_id',
        'user_id',
        'code',
        'external_code_id',
        'type',
        'valid_from',
        'valid_until',
        'status',
        'max_uses',
        'uses_count',
        'notified',
        'notified_at',
        'notes',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'notified' => 'boolean',
        'notified_at' => 'datetime',
    ];

    protected $hidden = [
        'code', // Hide actual code in API responses by default
    ];

    public function smartLock(): BelongsTo
    {
        return $this->belongsTo(SmartLock::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LockActivity::class);
    }

    public function isValid(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $now = now();

        if ($this->valid_from > $now) {
            return false;
        }

        if ($this->valid_until && $this->valid_until < $now) {
            return false;
        }

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function incrementUsage(): void
    {
        $this->increment('uses_count');

        if ($this->max_uses && $this->uses_count >= $this->max_uses) {
            $this->update(['status' => 'expired']);
        }
    }

    public static function generateUniqueCode(int $length = 6): string
    {
        do {
            $code = str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->where('status', '!=', 'expired')->exists());

        return $code;
    }

    public function getMaskedCodeAttribute(): string
    {
        if (strlen($this->code) <= 4) {
            return str_repeat('*', strlen($this->code));
        }

        return str_repeat('*', strlen($this->code) - 2) . substr($this->code, -2);
    }
}
