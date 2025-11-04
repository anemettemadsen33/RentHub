<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ScreeningDocument extends Model
{
    protected $fillable = [
        'guest_screening_id',
        'uploaded_by',
        'document_type',
        'document_number',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'verification_status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'issue_date',
        'expiry_date',
        'issuing_country',
        'issuing_authority',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'verified_at' => 'datetime',
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];

    public function screening(): BelongsTo
    {
        return $this->belongsTo(GuestScreening::class, 'guest_screening_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }
}
