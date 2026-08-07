<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\UserRole;
use App\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'SUPER_ADMIN' => [
                'dashboard.view',

                'users.view',
                'users.create',
                'users.update',
                'users.delete',

                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',

                'permissions.view',
                'modules.view',

                'customers.view',
                'customers.create',
                'customers.update',
                'customers.delete',

                'transactions.view',
                'transactions.export',

                'document_vault.view',
                'document_vault.download',

                'payslips.view',
                'payslips.generate',

                'reports.view',

                'settings.view',
                'settings.update',

                'sidebar.view',
            ],

            'ADMIN' => [

                'dashboard.view',

                'users.view',

                'customers.view',
                'customers.create',
                'customers.update',

                'transactions.view',

                'document_vault.view',

                'payslips.view',

                'reports.view',

                'settings.view',

                'sidebar.view',
            ],

            'CUSTOMER' => [

                'dashboard.view',

                'transactions.view',

                'document_vault.view',
                'document_vault.download',

                'payslips.view',

                'settings.view',
                'settings.update',

                'sidebar.view',
            ],
        ];

        foreach ($roles as $roleCode => $permissions) {

            $role = UserRole::where(
                'code',
                $roleCode
            )->first();

            if (! $role) {
                continue;
            }

            $permissionIds = Permission::query()
                ->whereIn('code', $permissions)
                ->pluck('id')
                ->toArray();

            $role->permissions()->sync(
                $permissionIds
            );
        }
    }
}