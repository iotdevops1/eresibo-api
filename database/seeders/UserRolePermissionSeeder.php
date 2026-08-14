<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = UserRole::where('code', 'SUPER_ADMIN')->first();

        // Give SUPER_ADMIN every permission
        $superAdmin->permissions()->sync(
            Permission::pluck('id')->toArray()
        );

        $admin = UserRole::where('code', 'ADMIN')->first();

        $adminPermissions = Permission::whereIn('code', [
            'users.view',
            'customers.view',
            'customers.create',
            'customers.update',
            'merchants.view',
            'merchants.create',
            'merchants.update',
            'transactions.view',
            'document_vault.view',
            'payslips.view',
            'reports.view',
            'settings.view',
            'permissions.view',
            'modules.view',
            'sidebar.view',
            'dashboard.view',
            'roles.view',
        ])->pluck('id')->toArray();

        $admin->permissions()->sync($adminPermissions);

        $customer = UserRole::where('code', 'CUSTOMER')->first();

        $customerPermissions = Permission::whereIn('code', [
            'dashboard.view',
            'sidebar.view',
            'transactions.view',
            'document_vault.view',
            'document_vault.download',
            'payslips.view',
            'settings.view',
            'settings.update',
        ])->pluck('id')->toArray();

        $customer->permissions()->sync($customerPermissions);
    }
}