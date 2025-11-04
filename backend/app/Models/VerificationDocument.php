<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class VerificationDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'document_type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'status',
        'rejection_reason',
        'uploaded_by',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'metadata',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'reviewed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Relationships
    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Helper Methods
    public function getFileUrl(): string
    {
        return Storage::url($this->file_path);
    }

    public function getFullPath(): string
    {
        return Storage::path($this->file_path);
    }

    public function fileExists(): bool
    {
        return Storage::exists($this->file_path);
    }

    public function approve(User $admin, ?string $notes = null): void
    {
        $this->status = 'approved';
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
    }

    public function reject(User $admin, string $reason, ?string $notes = null): void
    {
        $this->status = 'rejected';
        $this->rejection_reason = $reason;
        $this->reviewed_by = $admin->id;
        $this->reviewed_at = now();
        if ($notes) {
            $this->admin_notes = $notes;
        }
        $this->save();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        } elseif ($bytes > 1) {
            return $bytes.' bytes';
        } elseif ($bytes == 1) {
            return $bytes.' byte';
        } else {
            return '0 bytes';
        }
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
