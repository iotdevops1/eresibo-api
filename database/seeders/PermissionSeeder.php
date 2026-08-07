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