<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Services\Permission\PermissionService;
use App\Http\Resources\PermissionResource;
use App\Http\Requests\Role\UpdateRolePermissionsRequest;

class PermissionController extends BaseApiController
{
    public function __construct(
        protected PermissionService $permissionService
    ) {
    }

    public function index()
    {
        return $this->success(
            [
                'permissions' => PermissionResource::collection(
                    $this->permissionService->index()
                )
            ],
            'Permissions retrieved successfully.'
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
