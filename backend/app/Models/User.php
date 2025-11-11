<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Create default wishlist for new users
            $user->wishlists()->create([
                'name' => 'Favorites',
                'is_default' => true,
            ]);

            // Create default notification preference record (single row) for tests
            try {
                \App\Models\NotificationPreference::firstOrCreate([
                    'user_id' => $user->id,
                    'notification_type' => \App\Models\NotificationPreference::TYPE_ACCOUNT,
                ], [
                    'channel_email' => true,
                    'channel_database' => true,
                    'email_enabled' => true,
                    'sms_enabled' => false,
                    'push_enabled' => false,
                    'booking_updates' => true,
                    'payment_updates' => true,
                    'message_updates' => true,
                ]);
            } catch (\Throwable $e) {
                \Log::warning('Failed to auto-create notification preferences: '.$e->getMessage());
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'bio',
        'avatar',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'zip_code',
        'settings',
        'privacy_settings',
        'id_type',
        'id_number',
        'id_front_image',
        'id_back_image',
        'id_verification_status',
        'id_rejection_reason',
        'id_submitted_at',
        'is_verified',
        'verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_code',
        'id_number',
        'id_front_image',
        'id_back_image',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'phone_verification_code_expires_at' => 'datetime',
            'verified_at' => 'datetime',
            'profile_completed_at' => 'datetime',
            'two_factor_code_expires_at' => 'datetime',
            'identity_verified_at' => 'datetime',
            'government_id_verified_at' => 'datetime',
            'id_submitted_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'is_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'settings' => 'array',
            'privacy_settings' => 'array',
        ];
    }

    // Relationships
    public function properties()
    {
        return $this->hasMany(Property::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function receivedReviews()
    {
        return $this->hasManyThrough(Review::class, Property::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(BankAccount::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function notificationPreferences()
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->withPivot(['last_read_at', 'is_muted'])
            ->withTimestamps()
            ->orderBy('last_message_at', 'desc');
    }

    public function tenantConversations()
    {
        return $this->hasMany(Conversation::class, 'tenant_id');
    }

    public function ownerConversations()
    {
        return $this->hasMany(Conversation::class, 'owner_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function verification()
    {
        return $this->hasOne(UserVerification::class);
    }

    public function loyalty()
    {
        return $this->hasOne(UserLoyalty::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function referrals()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }

    public function referralsMade()
    {
        return $this->hasMany(Referral::class, 'referrer_id');
    }

    public function referralReceived()
    {
        return $this->hasOne(Referral::class, 'referred_id');
    }

    // Scopes
    public function scopeOwners($query)
    {
        return $query->where('role', 'owner');
    }

    public function scopeGuests($query)
    {
        return $query->where('role', 'guest');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    // Accessors
    public function getRoleAttribute($value)
    {
        // Compatibility: check pivot table first (spatie), fallback to column
        if ($this->relationLoaded('roles') && $this->roles->isNotEmpty()) {
            return $this->roles->first()->name;
        }

        return $value;
    }

    public function getIsOwnerAttribute()
    {
        return $this->role === 'owner';
    }

    public function getIsGuestAttribute()
    {
        return $this->role === 'guest';
    }

    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

    public function getAvatarUrlAttribute()
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Check if user has a specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user has any of the given roles
     */
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is owner
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if user is tenant
     */
    public function isTenant(): bool
    {
        return $this->role === 'tenant';
    }

    /**
     * Check if user is guest
     */
    public function isGuest(): bool
    {
        return $this->role === 'guest';
    }

    /**
     * Check if user has permission
     */
    public function hasPermission(string $permission): bool
    {
        $rolePermissions = [
            'admin' => ['full_access'],
            'owner' => [
                'browse_properties',
                'view_property_details',
                'list_properties',
                'manage_own_properties',
                'manage_bookings',
                'view_analytics',
                'send_messages',
                'respond_to_reviews',
            ],
            'tenant' => [
                'browse_properties',
                'view_property_details',
                'search_properties',
                'book_properties',
                'manage_own_bookings',
                'write_reviews',
                'send_messages',
            ],
            'guest' => [
                'browse_properties',
                'view_property_details',
                'search_properties',
            ],
        ];

        $permissions = $rolePermissions[$this->role] ?? [];

        return in_array('full_access', $permissions) || in_array($permission, $permissions);
    }

    /**
     * Get user permissions
     */
    public function getPermissions(): array
    {
        $rolePermissions = [
            'admin' => [
                'full_access',
                'manage_users',
                'manage_all_properties',
                'manage_all_bookings',
                'approve_verifications',
                'manage_settings',
                'view_all_analytics',
                'delete_reviews',
                'ban_users',
            ],
            'owner' => [
                'browse_properties',
                'view_property_details',
                'list_properties',
                'manage_own_properties',
                'manage_bookings',
                'view_analytics',
                'send_messages',
                'respond_to_reviews',
            ],
            'tenant' => [
                'browse_properties',
                'view_property_details',
                'search_properties',
                'book_properties',
                'manage_own_bookings',
                'write_reviews',
                'send_messages',
            ],
            'guest' => [
                'browse_properties',
                'view_property_details',
                'search_properties',
            ],
        ];

        return $rolePermissions[$this->role] ?? [];
    }
}
