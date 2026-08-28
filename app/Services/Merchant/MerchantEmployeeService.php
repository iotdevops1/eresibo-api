<?php

namespace App\Services\Merchant;

use App\Repositories\Merchant\MerchantEmployeeRepository;

class MerchantEmployeeService
{
    public function __construct(
        protected MerchantEmployeeRepository $merchantEmployeeRepository
    ) {
    }

    public function index(int $merchantId,array $filters = []) {
        return $this->merchantEmployeeRepository
            ->paginateByMerchant(
                $merchantId,
                $filters
            );
    }
}