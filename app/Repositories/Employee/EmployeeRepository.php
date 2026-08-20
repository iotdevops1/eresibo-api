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

    public function paginateByEmployer(
        int $employerId,
        array $filters
    ) {
        $query = $this->model
            ->newQuery()
            ->where('employer_id', $employerId);

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('employee_no', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('pusopay_wallet_id', 'like', "%{$search}%");
            });
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(
                $filters['per_page'] ?? 20
            );
    }

    public function findByUuidForEmployer(string $uuid, int $employerId): ?Employee {
        return $this->model
            ->newQuery()
            ->where('uuid', $uuid)
            ->where('employer_id', $employerId)
            ->first();
    }
}