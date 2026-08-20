<?php

namespace App\Repositories\Role;

use App\Models\UserRole;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class RoleRepository extends BaseRepository
{
    public function __construct(
         UserRole $model
    ) {
        $this->model = $model;
    }

    public function paginate()
    {
        return $this->model
            ->newQuery()
            ->latest()
            ->paginate(20);
    }

    public function findByCode(string $code): ?UserRole
    {
        return $this->model
            ->newQuery()
            ->where('code', $code)
            ->first();
    }

    public function findByUuid(string $uuid): ?UserRole
    {
        return $this->model
            ->newQuery()
            ->with('permissions')
            ->where('uuid', $uuid)
            ->first();
    }
    
    public function create(array $data): UserRole
    {
        return $this->model->create($data);
    }
}