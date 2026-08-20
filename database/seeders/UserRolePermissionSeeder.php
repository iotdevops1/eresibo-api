<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN
        |--------------------------------------------------------------------------
        */

        $superAdmin = UserRole::where('code', 'SUPER_ADMIN')->first();

        if ($superAdmin) {
            // SUPER_ADMIN receives every available permission.
            $superAdmin->permissions()->sync(
                Permission::pluck('id')->toArray()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN
        |--------------------------------------------------------------------------
        */

        $admin = UserRole::where('code', 'ADMIN')->first();

        if ($admin) {
            $adminPermissions = Permission::whereIn('code', [
                'dashboard.view',
                'sidebar.view',

                'users.view',

                'roles.view',

                'permissions.view',
                'modules.view',

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
            ])->pluck('id')->toArray();

            $admin->permissions()->sync($adminPermissions);
        }

        /*
        |--------------------------------------------------------------------------
        | CUSTOMER
        |--------------------------------------------------------------------------
        */

        $customer = UserRole::where('code', 'CUSTOMER')->first();

        if ($customer) {
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

        /*
        |--------------------------------------------------------------------------
        | EMPLOYER
        |--------------------------------------------------------------------------
        */

        $employer = UserRole::where('code', 'EMPLOYER')->first();

        if ($employer) {
            $employerPermissions = Permission::whereIn('code', [
                'dashboard.view',
                'sidebar.view',

                'insights.view',

                'payroll_batches.view',
                'payroll_batches.create',
                'payroll_batches.update',

                'disbursements.view',

                'fund_holds.view',
                'fund_holds.create',

                'payslips.view',
                'payslips.create',

                'team.view',
                'team.create',
                'team.update',
                'team.delete',
                
                'disputes.view',
                'disputes.create',

                'reports.view',

                'audit_logs.view',

                'settings.view',
                'settings.update',
            ])->pluck('id')->toArray();

            $employer->permissions()->sync($employerPermissions);
        }
    }
}