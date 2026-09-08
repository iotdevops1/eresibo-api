<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_FAILED = 3;
    public const STATUS_CANCELLED = 4;

    public const TYPE_PREFUND = 'PREFUND';
    public const TYPE_TRANSFER = 'TRANSFER';
    public const TYPE_PAYROLL = 'PAYROLL';
    public const TYPE_PAYOUT = 'PAYOUT';
    public const TYPE_CASH_IN = 'CASH_IN';
    public const TYPE_CASH_OUT = 'CASH_OUT';
    public const TYPE_REFUND = 'REFUND';
    public const TYPE_FEE = 'FEE';
    public const TYPE_ADJUSTMENT = 'ADJUSTMENT';
    public const TYPE_FUNDING = 'FUNDING';

    protected $fillable = [
        'uuid',
        'reference',
        'type',
        'status',
        'amount_minor_units',
        'amount_major_units',
        'currency',
        'from_wallet_id',
        'to_wallet_id',
        'description',
        'metadata',
        'completed_at',
    ];

    protected $casts = [
        'amount_minor_units' => 'integer',
        'amount_major_units' => 'decimal:2',
        'metadata' => 'array',
        'completed_at' => 'datetime',
        'status' => 'integer',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function fromWallet()
    {
        return $this->belongsTo(
            Wallet::class,
            'from_wallet_id'
        );
    }

    public function toWallet()
    {
        return $this->belongsTo(
            Wallet::class,
            'to_wallet_id'
        );
    }

    public function entries()
    {
        return $this->hasMany(
            WalletEntry::class,
            'wallet_transaction_id'
        );
    }
}