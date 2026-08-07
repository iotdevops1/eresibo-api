<?php

namespace App\Repositories\Permission;

use App\Models\Permission;
use App\Repositories\BaseRepository;

class PermissionRepository extends BaseRepository
{
    public function __construct(
        Permission $model
    ) {
        $this->model = $model;
    }

    public function all()
    {
        return $this->model
            ->newQuery()
            ->orderBy('module')
            ->orderBy('name')
            ->get();
    }
}