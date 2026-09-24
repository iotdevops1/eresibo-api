<?php

namespace App\Repositories\Wallet;

use App\Models\WalletTransaction;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WalletTransactionRepository extends BaseRepository
{
    public function __construct(WalletTransaction $model)
    {
        $this->model = $model;
    }

    public function paginateForWalletIds(array $walletIds, array $filters = []): LengthAwarePaginator
    {
        $query = $this->queryForWalletIds($walletIds);

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('completed_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('completed_at', '<=', $filters['to_date']);
        }

        return $query
            ->orderByDesc('completed_at')
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function findByUuidForWalletIds(string $uuid, array $walletIds): ?WalletTransaction
    {
        return $this->queryForWalletIds($walletIds)
            ->where('uuid', $uuid)
            ->first();
    }

    private function queryForWalletIds(array $walletIds)
    {
        return $this->model->newQuery()
            ->where(function ($query) use ($walletIds) {
                $query->whereIn('from_wallet_id', $walletIds)
                    ->orWhereIn('to_wallet_id', $walletIds);
            })
            ->with(['fromWallet', 'toWallet']);
    }
}
