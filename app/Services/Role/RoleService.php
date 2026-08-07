<?php

namespace App\Services\Role;

use App\Models\UserRole;
use App\Repositories\Role\RoleRepository;

class RoleService
{
    public function __construct(
        protected RoleRepository $roleRepository
    ) {
    }

    public function index()
    {
        return $this->roleRepository->paginate();
    }

    public function show(string $uuid): UserRole
    {
        $role = $this->roleRepository
            ->findByUuid($uuid);

        if (! $role) {
            abort(404, 'Role not found.');
        }

        return $role;
    }
}