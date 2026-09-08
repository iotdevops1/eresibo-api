<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WalletMerchantFunding;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Wallet;
use App\Models\Merchant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasUuids, SoftDeletes;

    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;
    public const STATUS_LOCKED = 3;

    public const OWNER_TYPE_MAIN = 'main';
    public const OWNER_TYPE_MERCHANT = 'merchant';
    public const OWNER_TYPE_USER = 'user';

    protected $fillable = [
        'uuid',
        'name',
        'owner_type',
        'owner_id',
        'currency',
        'balance_minor_units',
        'balance_major_units',
        'status',
    ];

    protected $casts = [
        'balance_minor_units' => 'integer',
        'balance_major_units' => 'decimal:2',
        'status' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function owner()
    {
        return $this->morphTo();
    }

    public function entries()
    {
        return $this->hasMany(
            WalletEntry::class,
            'wallet_id'
        );
    }

    public function merchantFundings()
    {
        return $this->hasMany(
            WalletMerchantFunding::class,
            'wallet_id'
        );
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'owner_id')
            ->where('owner_type', Wallet::OWNER_TYPE_MERCHANT);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(
            Merchant::class,
            'owner_id'
        );
    }
}