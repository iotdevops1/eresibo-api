<?php

namespace App\Repositories\Merchant;

use App\Models\Employee;

class MerchantEmployeeRepository
{
    public function paginateByMerchant(
        int $merchantId,
        array $filters = []
    ) {
        $query = Employee::query()
            ->with([
                'merchant',
            ])
            ->where('merchant_id', $merchantId);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where(
                    'employee_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'first_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'middle_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'last_name',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'email',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'mobile',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'pusopay_wallet_id',
                    'like',
                    "%{$search}%"
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Status
        |--------------------------------------------------------------------------
        */

        if (isset($filters['status'])) {
            $query->where(
                'status',
                $filters['status']
            );
        }

        return $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }
}