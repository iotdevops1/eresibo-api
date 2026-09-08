<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WalletEntry extends Model
{
    use HasUuids;

    public const TYPE_CREDIT = 'CREDIT';
    public const TYPE_DEBIT = 'DEBIT';

    protected $fillable = [
        'uuid',
        'wallet_transaction_id',
        'wallet_id',
        'entry_type',
        'amount_minor_units',
        'amount_major_units',
        'balance_before_minor_units',
        'balance_before_major_units',
        'balance_after_minor_units',
        'balance_after_major_units',
    ];

    protected $casts = [
        'amount_minor_units' => 'integer',
        'amount_major_units' => 'decimal:2',

        'balance_before_minor_units' => 'integer',
        'balance_before_major_units' => 'decimal:2',

        'balance_after_minor_units' => 'integer',
        'balance_after_major_units' => 'decimal:2',
    ];

    public function uniqueIds(): array
    {
        return ['uuid'];
    }

    public function walletTransaction()
    {
        return $this->belongsTo(
            WalletTransaction::class,
            'wallet_transaction_id'
        );
    }

    public function wallet()
    {
        return $this->belongsTo(
            Wallet::class,
            'wallet_id'
        );
    }

    public function isCredit(): bool
    {
        return $this->entry_type === self::TYPE_CREDIT;
    }

    public function isDebit(): bool
    {
        return $this->entry_type === self::TYPE_DEBIT;
    }
}