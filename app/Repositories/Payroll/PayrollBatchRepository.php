<?php

namespace App\Repositories\Payroll;

use App\Models\PayrollBatch;
use App\Repositories\BaseRepository;

class PayrollBatchRepository extends BaseRepository
{
    public function __construct(PayrollBatch $model)
    {
        $this->model = $model;
    }

    public function paginateByMerchant(
        int $merchantId,
        array $filters = []
    ) {
        $query = $this->model
            ->newQuery()
            ->where('merchant_id', $merchantId)
            ->withCount('items')
            ->with([
                'merchant',
                'items.employee',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['search'])) {

            $search = $filters['search'];

            $query->where(function ($q) use ($search) {

                $q->where(
                    'batch_no',
                    'like',
                    "%{$search}%"
                )
                ->orWhere(
                    'description',
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
        | Pay Date
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['pay_date'])) {
            $query->whereDate(
                'pay_date',
                $filters['pay_date']
            );
        }

        return $query
            ->orderByDesc('id')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    public function findByUuidForMerchant(
        string $uuid,
        int $merchantId
    ): ?PayrollBatch {
        return $this->model
            ->newQuery()
            ->where('uuid', $uuid)
            ->where('merchant_id', $merchantId)
            ->with([
                'merchant',
                'items.employee',
            ])
            ->first();
    }
}