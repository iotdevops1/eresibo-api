<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's permissions.
     */
    public function run(): void
    {
        $permissions = [

        /*
        |--------------------------------------------------------------------------
        | Dashboard
        |--------------------------------------------------------------------------
        */
        ['module' => 'DASHBOARD', 'code' => 'dashboard.view', 'name' => 'View Dashboard'],

        /*
        |--------------------------------------------------------------------------
        | Users
        |--------------------------------------------------------------------------
        */
        ['module' => 'USERS', 'code' => 'users.view', 'name' => 'View Users'],
        ['module' => 'USERS', 'code' => 'users.create', 'name' => 'Create User'],
        ['module' => 'USERS', 'code' => 'users.update', 'name' => 'Update User'],
        ['module' => 'USERS', 'code' => 'users.delete', 'name' => 'Delete User'],

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        ['module' => 'USER_ROLES', 'code' => 'roles.view', 'name' => 'View Roles'],
        ['module' => 'USER_ROLES', 'code' => 'roles.create', 'name' => 'Create Role'],
        ['module' => 'USER_ROLES', 'code' => 'roles.update', 'name' => 'Update Role'],
        ['module' => 'USER_ROLES', 'code' => 'roles.delete', 'name' => 'Delete Role'],

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */
        ['module' => 'PERMISSIONS', 'code' => 'permissions.view', 'name' => 'View Permissions'],

        /*
        |--------------------------------------------------------------------------
        | Modules
        |--------------------------------------------------------------------------
        */
        ['module' => 'MODULES', 'code' => 'modules.view', 'name' => 'View Modules'],

        /*
        |--------------------------------------------------------------------------
        | Customers
        |--------------------------------------------------------------------------
        */
        ['module' => 'CUSTOMERS', 'code' => 'customers.view', 'name' => 'View Customers'],
        ['module' => 'CUSTOMERS', 'code' => 'customers.create', 'name' => 'Create Customer'],
        ['module' => 'CUSTOMERS', 'code' => 'customers.update', 'name' => 'Update Customer'],
        ['module' => 'CUSTOMERS', 'code' => 'customers.delete', 'name' => 'Delete Customer'],

        /*
        |--------------------------------------------------------------------------
        | Merchant
        |--------------------------------------------------------------------------
        */
        ['module' => 'MECHANT', 'code' => 'merchants.view',   'name' => 'View merchant'],
        ['module' => 'MECHANT', 'code' => 'merchants.create', 'name' => 'Update merchant'],
        ['module' => 'MECHANT', 'code' => 'merchants.update', 'name' => 'Update merchant'],
        ['module' => 'MECHANT', 'code' => 'merchants.delete', 'name' => 'Update merchant'],
        /*
        |--------------------------------------------------------------------------
        | Transactions
        |--------------------------------------------------------------------------
        */
        ['module' => 'TRANSACTIONS', 'code' => 'transactions.view', 'name' => 'View Transactions'],
        ['module' => 'TRANSACTIONS', 'code' => 'transactions.export', 'name' => 'Export Transactions'],

        /*
        |--------------------------------------------------------------------------
        | Document Vault
        |--------------------------------------------------------------------------
        */
        ['module' => 'DOCUMENT_VAULT', 'code' => 'document_vault.view', 'name' => 'View Documents'],
        ['module' => 'DOCUMENT_VAULT', 'code' => 'document_vault.download', 'name' => 'Download Documents'],

        /*
        |--------------------------------------------------------------------------
        | Payslips
        |--------------------------------------------------------------------------
        */
        ['module' => 'PAYSLIPS', 'code' => 'payslips.view', 'name' => 'View Payslips'],
        ['module' => 'PAYSLIPS', 'code' => 'payslips.generate', 'name' => 'Generate Payslips'],

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */
        ['module' => 'REPORTS', 'code' => 'reports.view', 'name' => 'View Reports'],

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        ['module' => 'SETTINGS', 'code' => 'settings.view', 'name' => 'View Settings'],
        ['module' => 'SETTINGS', 'code' => 'settings.update', 'name' => 'Update Settings'],

        /*
        |--------------------------------------------------------------------------
        | System
        |--------------------------------------------------------------------------
        */
        ['module' => 'SYSTEM', 'code' => 'permissions.view', 'name' => 'View Permissions'],
        ['module' => 'SYSTEM', 'code' => 'modules.view', 'name' => 'View Modules'],
        ['module' => 'SYSTEM', 'code' => 'sidebar.view', 'name' => 'View Sidebar'],

        /*
        |--------------------------------------------------------------------------
        | Employer
        |--------------------------------------------------------------------------
        */

        ['module' => 'EMPLOYER', 'code' => 'insights.view', 'name' => 'View Insights'],

        ['module' => 'EMPLOYER', 'code' => 'payroll_batches.view', 'name' => 'View Payroll Batches'],
        ['module' => 'EMPLOYER', 'code' => 'payroll_batches.create', 'name' => 'Create Payroll Batch'],
        ['module' => 'EMPLOYER', 'code' => 'payroll_batches.update', 'name' => 'Update Payroll Batch'],

        ['module' => 'EMPLOYER', 'code' => 'disbursements.view', 'name' => 'View Disbursements'],

        ['module' => 'EMPLOYER', 'code' => 'fund_holds.view', 'name' => 'View Fund Holds'],
        ['module' => 'EMPLOYER', 'code' => 'fund_holds.create', 'name' => 'Create Fund Hold'],

        ['module' => 'EMPLOYER', 'code' => 'payslips.view', 'name' => 'View Payslips'],
        ['module' => 'EMPLOYER', 'code' => 'payslips.create', 'name' => 'Create Payslip'],

        ['module' => 'EMPLOYER', 'code' => 'team.view', 'name' => 'View Team Directory'],
        ['module' => 'EMPLOYER', 'code' => 'team.create', 'name' => 'Add Team Member'],
        ['module' => 'EMPLOYER', 'code' => 'team.update', 'name' => 'Update Team Member'],

        ['module' => 'EMPLOYER', 'code' => 'disputes.view', 'name' => 'View Disputes'],
        ['module' => 'EMPLOYER', 'code' => 'disputes.create', 'name' => 'Create Dispute'],

        ['module' => 'EMPLOYER', 'code' => 'reports.view', 'name' => 'View Reports'],

        ['module' => 'EMPLOYER', 'code' => 'audit_logs.view', 'name' => 'View Audit Logs'],

    ];

        foreach ($permissions as $permission) {

            Permission::updateOrCreate(
                [
                    'code' => $permission['code'],
                ],
                [
                    'module' => $permission['module'],
                    'name' => $permission['name'],
                    'description' => null,
                    'active' => true,
                ]
            );

        }
    }
}