<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\Role\RoleService;
use App\Http\Controllers\BaseApiController;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;

class RoleController extends BaseApiController
{
    public function __construct(
        protected RoleService $roleService
    ) {
    }

    public function index()
    {
        return $this->success(
            new RoleCollection(
                $this->roleService->index()
            ),
            'Roles retrieved successfully.'
        );
    }

    public function show(string $uuid)
    {
        return $this->success(
            new RoleResource(
                $this->roleService->show($uuid)
            ),
            'Role retrieved successfully.'
        );
    }
}