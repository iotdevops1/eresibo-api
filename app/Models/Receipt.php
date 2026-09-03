<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receipt extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_CONFIRMED = 1;
    public const STATUS_FAILED = 2;

    protected $fillable = [
        'uuid',
        'source_system',
        'external_reference',
        'amount_minor',
        'currency',
        'transaction_type',
        'counterparty_label',
        'occurred_at',
        'public_token',
        'expires_at',
        'status',
        'processed_at',
        'failure_reason',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'occurred_at' => 'datetime',
        'expires_at' => 'datetime',
        'processed_at' => 'datetime',
        'status' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function getPublicUrlAttribute(): string
    {
        return rtrim(config('services.portal.url'), '/') . '/r/' . $this->public_token;
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }
}