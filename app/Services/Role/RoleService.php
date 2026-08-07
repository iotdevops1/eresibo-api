<?php

namespace App\Services\Role;

use App\Models\UserRole;
use App\Repositories\Role\RoleRepository;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

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

    public function store(array $data): UserRole
    {
        return DB::transaction(function () use ($data) {

            return $this->roleRepository->create([
                'code'        => strtoupper($data['code']),
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'active'      => $data['active'],
            ]);
        });
    }

    public function update(string $uuid, array $data): UserRole
    {
        $role = $this->roleRepository
            ->findByUuid($uuid);

        if (! $role) {
            abort(404, 'Role not found.');
        }

        return DB::transaction(function () use ($role, $data) {
            return $this->roleRepository->update($role, [
                'code'        => strtoupper($data['code']),
                'name'        => $data['name'],
                'description' => $data['description'] ?? null,
                'active'      => $data['active'],
            ]);
        });
    }

    public function destroy(string $uuid): void
    {
        $role = $this->roleRepository->findByUuid($uuid);

        if (! $role) {
            abort(404, 'Role not found.');
        }

        if (in_array($role->code, ['SUPER_ADMIN', 'ADMIN', 'CUSTOMER'])) {
            abort(422, 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            abort(422, 'Role is currently assigned to users.');
        }

        $this->roleRepository->delete($role);
    }

    public function updatePermissions(string $uuid, array $permissionCodes): UserRole {

        return DB::transaction(function () use ($uuid, $permissionCodes) {
            $role = $this->roleRepository
                ->findByUuid($uuid);

            if (! $role) {
                abort(404, 'Role not found.');
            }

            $permissionIds = Permission::query()
                ->whereIn('code', $permissionCodes)
                ->pluck('id')
                ->toArray();

            $role->permissions()->sync(
                $permissionIds
            );

            return $role->load('permissions');
        });
    }
}