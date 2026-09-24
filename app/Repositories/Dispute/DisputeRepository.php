<?php

namespace App\Repositories\Dispute;

use App\Models\Dispute;
use App\Repositories\BaseRepository;

class DisputeRepository extends BaseRepository
{
    public function __construct(Dispute $model)
    {
        $this->model = $model;
    }

    public function paginateForEmployeeUser(int $userId, array $filters = [])
    {
        return $this->applyFilters(
            $this->model->newQuery()
                ->whereHas('employee', fn ($query) => $query->where('user_id', $userId)),
            $filters
        )->with($this->relations())
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function paginateForMerchant(int $merchantId, array $filters = [])
    {
        return $this->applyFilters(
            $this->model->newQuery()->where('merchant_id', $merchantId),
            $filters
        )->with($this->relations())
            ->orderByDesc('id')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function findForEmployeeUser(string $uuid, int $userId): ?Dispute
    {
        return $this->model->newQuery()
            ->where('uuid', $uuid)
            ->whereHas('employee', fn ($query) => $query->where('user_id', $userId))
            ->with([...$this->relations(), 'messages.user'])
            ->first();
    }

    public function findForMerchant(string $uuid, int $merchantId): ?Dispute
    {
        return $this->model->newQuery()
            ->where('uuid', $uuid)
            ->where('merchant_id', $merchantId)
            ->with([...$this->relations(), 'messages.user'])
            ->first();
    }

    private function applyFilters($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';

            $query->where(function ($searchQuery) use ($search) {
                $searchQuery->where('subject', 'like', $search)
                    ->orWhere('description', 'like', $search)
                    ->orWhere('reference', 'like', $search)
                    ->orWhereHas(
                        'merchant',
                        fn ($merchantQuery) => $merchantQuery->where('business_name', 'like', $search)
                    );
            });
        }

        if (! empty($filters['employee_uuid'])) {
            $query->whereHas(
                'employee',
                fn ($employeeQuery) => $employeeQuery->where('uuid', $filters['employee_uuid'])
            );
        }

        return $query;
    }

    private function relations(): array
    {
        return ['merchant', 'employee', 'payslip', 'createdBy', 'resolvedBy'];
    }
}
