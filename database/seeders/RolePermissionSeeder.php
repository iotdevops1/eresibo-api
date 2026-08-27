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

            /*
            |--------------------------------------------------------------------------
            | SUPER ADMIN
            |--------------------------------------------------------------------------
            */

            'SUPER_ADMIN' => [

                'dashboard.view',
                'sidebar.view',

                // Users
                'users.view',
                'users.create',
                'users.update',
                'users.delete',

                // Roles
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',

                // Permissions / Modules
                'permissions.view',
                'modules.view',

                // Customers
                'customers.view',
                'customers.create',
                'customers.update',
                'customers.delete',

                // Merchants
                'merchants.view',
                'merchants.create',
                'merchants.update',
                'merchants.delete',

                // Merchant Management
                'management.view',
                'management.create',
                'management.update',
                'management.delete',

                // Prefunding
                'prefunding.view',
                'prefunding.create',
                'prefunding.update',
                'prefunding.delete',

                // Transactions
                'transactions.view',
                'transactions.export',

                // Documents
                'document_vault.view',
                'document_vault.download',

                // Payslips
                'payslips.view',
                'payslips.generate',

                // Reports
                'reports.view',

                // Settings
                'settings.view',
                'settings.update',
            ],

            /*
            |--------------------------------------------------------------------------
            | ADMIN
            |--------------------------------------------------------------------------
            */

            'ADMIN' => [

                'dashboard.view',
                'sidebar.view',

                // Users
                'users.view',

                // Customers
                'customers.view',
                'customers.create',
                'customers.update',

                // Merchants
                'merchants.view',
                'merchants.create',
                'merchants.update',

                // Merchant Management
                'management.view',
                'management.create',
                'management.update',

                // Prefunding
                'prefunding.view',
                'prefunding.create',
                'prefunding.update',

                // Transactions
                'transactions.view',

                // Documents
                'document_vault.view',

                // Payslips
                'payslips.view',

                // Reports
                'reports.view',

                // Permissions / Modules
                'permissions.view',
                'modules.view',

                // Roles
                'roles.view',

                // Settings
                'settings.view',
            ],

            /*
            |--------------------------------------------------------------------------
            | CUSTOMER
            |--------------------------------------------------------------------------
            */

            'CUSTOMER' => [

                'dashboard.view',
                'sidebar.view',

                'transactions.view',

                'document_vault.view',
                'document_vault.download',

                'payslips.view',

                'settings.view',
                'settings.update',
            ],

            /*
            |--------------------------------------------------------------------------
            | EMPLOYER
            |--------------------------------------------------------------------------
            */

            'EMPLOYER' => [

                'dashboard.view',
                'sidebar.view',

                // Employer dashboard
                'insights.view',

                // Payroll
                'payroll_batches.view',
                'payroll_batches.create',
                'payroll_batches.update',
                'payroll_batches.submit',

                // Disbursements
                'disbursements.view',

                // Fund holds
                'fund_holds.view',
                'fund_holds.create',

                // Payslips
                'payslips.view',
                'payslips.create',

                // Team Directory
                'team.view',
                'team.create',
                'team.update',
                'team.delete',

                // Disputes
                'disputes.view',
                'disputes.create',

                // Reports
                'reports.view',

                // Audit
                'audit_logs.view',

                // Settings
                'settings.view',
                'settings.update',
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