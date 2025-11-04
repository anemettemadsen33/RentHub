<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'guest_verification_id',
        'user_id',
        'verification_type',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];

    // Relationships
    public function guestVerification(): BelongsTo
    {
        return $this->belongsTo(GuestVerification::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper Methods
    public static function log(
        int $guestVerificationId,
        string $verificationType,
        string $action,
        ?string $details = null,
        ?int $userId = null
    ): self {
        return self::create([
            'guest_verification_id' => $guestVerificationId,
            'user_id' => $userId,
            'verification_type' => $verificationType,
            'action' => $action,
            'details' => $details,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
