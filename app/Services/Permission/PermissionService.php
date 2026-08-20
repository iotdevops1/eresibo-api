<?php

namespace App\Services\Permission;

use App\Repositories\Permission\PermissionRepository;

class PermissionService
{
    public function __construct(
        protected PermissionRepository $permissionRepository
    ) {
    }

    public function index()
    {
        return $this->permissionRepository->all();
    }
}