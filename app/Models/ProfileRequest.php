<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfileRequest extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'name',
        'email',
        'password',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPasswordRequest(): bool
    {
        return $this->type === 'password';
    }

    public function isProfileRequest(): bool
    {
        return $this->type === 'profile';
    }

    public function typeLabel(): string
    {
        return $this->isPasswordRequest() ? 'Password Reset' : 'Profile Change';
    }
}