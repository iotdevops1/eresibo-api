<?php

namespace App\Http\Controllers\Api\Admin;

use App\Services\Role\RoleService;
use App\Http\Controllers\BaseApiController;
use App\Http\Resources\RoleCollection;
use App\Http\Resources\RoleResource;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Requests\Role\UpdateRolePermissionsRequest;

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

    public function store(StoreRoleRequest $request)
    {
        $role = $this->roleService->store(
            $request->validated()
        );

        return $this->success(
            new RoleResource($role),
            'Role created successfully.',
            201
        );
    }

    public function update(UpdateRoleRequest $request, string $uuid)
    {
        $role = $this->roleService->update(
            $uuid,
            $request->validated()
        );

        return $this->success(
            new RoleResource($role),
            'Role updated successfully.'
        );
    }

    public function destroy(string $uuid)
    {
        $this->roleService->destroy($uuid);

        return $this->success(
            null,
            'Role deleted successfully.'
        );
    }

    public function updatePermissions(string $uuid, UpdateRolePermissionsRequest $request)
    {
        $role = $this->roleService->updatePermissions(
            $uuid,
            $request->validated()['permissions']
        );

        return $this->success(
            new RoleResource($role),
            'Role permissions updated successfully.'
        );
    }
}