<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class WalletMerchantFunding extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_PENDING = 1;
    public const STATUS_CONFIRMED = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_CANCELLED = 4;

    protected $fillable = [
        'uuid',
        'wallet_id',
        'provider',
        'external_reference',
        'amount_minor_units',
        'amount_major_units',
        'currency',
        'status',
        'metadata',
        'confirmed_at',
    ];

    protected $casts = [
        'amount_minor_units' => 'integer',
        'amount_major_units' => 'decimal:2',
        'status' => 'integer',
        'metadata' => 'array',
        'confirmed_at' => 'datetime',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }
}