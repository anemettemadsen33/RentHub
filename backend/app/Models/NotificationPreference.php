<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'channel_email',
        'channel_database',
        'channel_sms',
        'channel_push',
    ];

    protected $casts = [
        'channel_email' => 'boolean',
        'channel_database' => 'boolean',
        'channel_sms' => 'boolean',
        'channel_push' => 'boolean',
    ];

    // Available notification types
    public const TYPE_BOOKING = 'booking';
    public const TYPE_PAYMENT = 'payment';
    public const TYPE_REVIEW = 'review';
    public const TYPE_ACCOUNT = 'account';
    public const TYPE_SYSTEM = 'system';

    public static function types(): array
    {
        return [
            self::TYPE_BOOKING,
            self::TYPE_PAYMENT,
            self::TYPE_REVIEW,
            self::TYPE_ACCOUNT,
            self::TYPE_SYSTEM,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Get user's preferences for a specific type
    public static function getForUser(int $userId, string $type): ?self
    {
        return static::where('user_id', $userId)
            ->where('notification_type', $type)
            ->first();
    }

    // Get or create default preferences
    public static function getOrCreateDefaults(int $userId, string $type): self
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'notification_type' => $type,
            ],
            [
                'channel_email' => true,
                'channel_database' => true,
                'channel_sms' => false,
                'channel_push' => false,
            ]
        );
    }

    // Check if user has channel enabled for notification type
    public static function isChannelEnabled(int $userId, string $type, string $channel): bool
    {
        $preference = static::getForUser($userId, $type);
        
        if (!$preference) {
            // Default to enabled if no preference set
            return in_array($channel, ['email', 'database']);
        }

        return (bool) $preference->{"channel_{$channel}"};
    }

    // Get all enabled channels for user and type
    public static function getEnabledChannels(int $userId, string $type): array
    {
        $preference = static::getOrCreateDefaults($userId, $type);
        $channels = [];

        if ($preference->channel_email) {
            $channels[] = 'mail';
        }
        if ($preference->channel_database) {
            $channels[] = 'database';
        }
        if ($preference->channel_sms) {
            $channels[] = 'sms';
        }
        if ($preference->channel_push) {
            $channels[] = 'broadcast';
        }

        return $channels;
    }
}
