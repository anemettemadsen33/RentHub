<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;
    protected $fillable = [
        'property_id',
        'booking_id',
        'tenant_id',
        'owner_id',
        'subject',
        'last_message_at',
        'is_archived',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'is_archived' => 'boolean',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tenant_id');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at', 'asc');
    }

    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest();
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'is_muted'])
            ->withTimestamps();
    }

    public function unreadCount(User $user): int
    {
        $participant = $this->participants()->where('user_id', $user->id)->first();

        if (! $participant) {
            return 0;
        }

        return $this->messages()
            ->where('sender_id', '!=', $user->id)
            ->where(function ($query) use ($participant) {
                $query->whereNull($participant->pivot->last_read_at)
                    ->orWhere('created_at', '>', $participant->pivot->last_read_at);
            })
            ->count();
    }

    public function markAsRead(User $user): void
    {
        $this->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);
    }

    public function getOtherParticipant(User $user): User
    {
        return $user->id === $this->tenant_id ? $this->owner : $this->tenant;
    }
}
