<?php

namespace App\Repositories\Employee;

use App\Models\Employee;
use App\Repositories\BaseRepository;

class EmployeeRepository extends BaseRepository
{
    public function __construct(Employee $model)
    {
        $this->model = $model;
    }

    /**
     * Get employees belonging to a merchant.
     */
    public function paginateByMerchant(int $merchantId, array $filters = []) {
        $query = $this->model
            ->newQuery()
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

        /*
        |--------------------------------------------------------------------------
        | Sorting / Pagination
        |--------------------------------------------------------------------------
        */

        return $query
            ->with('user')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    /**
     * Find an employee belonging to a merchant.
     */
    public function findByUuidForMerchant(string $uuid, int $merchantId): ?Employee {
        return $this->model
            ->newQuery()
            ->where('uuid', $uuid)
            ->where('merchant_id', $merchantId)
            ->first();
    }
}