<?php
namespace App\Repositories\Wallet;

use App\Models\Wallet;
use App\Repositories\BaseRepository;

class WalletRepository extends BaseRepository {
    public function __construct(Wallet $model) { $this->model=$model; }

    public function findUserWallet(int $userId,string $currency='PHP'): ?Wallet {
        return $this->model->newQuery()
            ->where('owner_type',Wallet::OWNER_TYPE_USER)
            ->where('owner_id',$userId)
            ->where('currency',$currency)
            ->first();
    }

    public function findMerchantWallet(int $merchantId,string $currency='PHP'): ?Wallet {
        return $this->model->newQuery()
            ->where('owner_type',Wallet::OWNER_TYPE_MERCHANT)
            ->where('owner_id',$merchantId)
            ->where('currency',$currency)
            ->first();
    }
}
