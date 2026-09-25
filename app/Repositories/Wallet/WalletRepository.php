<?php

namespace App\Repositories\Wallet;

use App\Models\Wallet;
use App\Repositories\BaseRepository;

class WalletRepository extends BaseRepository
{
    public function __construct(Wallet $model)
    {
        $this->model = $model;
    }

    public function findUserWallet(int $userId, string $currency = 'PHP', bool $includeDeleted = false): ?Wallet
    {
        return $this->model->newQuery()
            ->withTrashed($includeDeleted)
            ->where('owner_type', Wallet::OWNER_TYPE_USER)
            ->where('owner_id', $userId)
            ->where('currency', $currency)
            ->first();
    }

    public function firstOrCreateUserWallet(
        int $userId,
        string $name,
        int $status = Wallet::STATUS_ACTIVE,
        string $currency = 'PHP',
    ): Wallet {
        // Include deleted wallets so provisioning never restores or replaces them.
        // The owner/currency unique index also protects concurrent creation.
        return $this->model->newQuery()->withTrashed()->firstOrCreate([
            'owner_type' => Wallet::OWNER_TYPE_USER,
            'owner_id' => $userId,
            'currency' => $currency,
        ], [
            'name' => $name,
            'balance_minor_units' => 0,
            'balance_major_units' => '0.00',
            'held_minor_units' => 0,
            'held_major_units' => '0.00',
            'status' => $status,
        ]);
    }

    public function findMerchantWallet(int $merchantId, string $currency = 'PHP'): ?Wallet
    {
        return $this->model->newQuery()
            ->where('owner_type', Wallet::OWNER_TYPE_MERCHANT)
            ->where('owner_id', $merchantId)
            ->where('currency', $currency)
            ->first();
    }
}
