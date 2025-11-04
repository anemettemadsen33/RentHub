<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OAuthClient extends Model
{
    protected $fillable = [
        'client_id',
        'client_secret',
        'name',
        'redirect_uri',
        'is_confidential',
        'is_active',
    ];

    protected $hidden = [
        'client_secret',
    ];

    protected $casts = [
        'is_confidential' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function accessTokens(): HasMany
    {
        return $this->hasMany(OAuthAccessToken::class, 'client_id');
    }

    public function refreshTokens(): HasMany
    {
        return $this->hasMany(OAuthRefreshToken::class, 'client_id');
    }
}
