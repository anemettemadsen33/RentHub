<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OAuthProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider',
        'provider_id',
        'access_token',
        'refresh_token',
        'token_expires_at',
        'last_login_at',
    ];

    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_login_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the user that owns the OAuth provider
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
