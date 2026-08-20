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

    public function paginateByEmployer(int $employerId, array $filters) {
        $query = $this->model
            ->newQuery()
            ->where('employer_id', $employerId)
            ->withCount('items')
            ->with([
                'items.employee',
            ]);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('batch_no', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

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

    public function findByUuidForEmployer(string $uuid, int $employerId): ?PayrollBatch {
        return $this->model
            ->newQuery()
            ->where('uuid', $uuid)
            ->where('employer_id', $employerId)
            ->with([
                'items.employee',
            ])
            ->first();
    }
}