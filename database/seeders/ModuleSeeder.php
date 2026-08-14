<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Customer Menus
        |--------------------------------------------------------------------------
        */

        Module::updateOrCreate(
            ['code' => 'DASHBOARD'],
            [
                'name' => 'Dashboard',
                'icon' => 'layout-dashboard',
                'route' => '/dashboard',
                'permission_code' => 'dashboard.view',
                'sort_order' => 1,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'TRANSACTIONS'],
            [
                'name' => 'Transactions',
                'icon' => 'receipt',
                'route' => '/transactions',
                'permission_code' => 'transactions.view',
                'sort_order' => 2,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'PAYSLIPS'],
            [
                'name' => 'Payslips',
                'icon' => 'file-text',
                'route' => '/payslips',
                'permission_code' => 'payslips.view',
                'sort_order' => 3,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'FINANCIAL_SUMMARY'],
            [
                'name' => 'Financial Summary',
                'icon' => 'chart-column',
                'route' => '/financial-summary',
                'permission_code' => 'reports.view',
                'sort_order' => 4,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'MY_CASES'],
            [
                'name' => 'My Cases',
                'icon' => 'briefcase',
                'route' => '/my-cases',
                'sort_order' => 5,
                'active' => true,
            ]
        );

       Module::updateOrCreate(
            ['code' => 'DOCUMENT_VAULT'],
            [
                'name' => 'Document Vault',
                'icon' => 'folder-lock',
                'route' => '/document-vault',
                'permission_code' => 'document_vault.view',
                'sort_order' => 6,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'SETTINGS'],
            [
                'name' => 'Settings',
                'icon' => 'settings',
                'route' => '/settings',
                'permission_code' => 'settings.view',
                'sort_order' => 7,
                'active' => true,
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Administration
        |--------------------------------------------------------------------------
        */

        $administration = Module::updateOrCreate(
            ['code' => 'ADMINISTRATION'],
            [
                'name' => 'Administration',
                'icon' => 'shield',
                'route' => null,
                'permission_code' => null,
                'sort_order' => 999,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'USERS'],
            [
                'name' => 'Users',
                'icon' => 'users',
                'route' => '/admin/users',
                'permission_code' => 'users.view',
                'parent_id' => $administration->id,
                'sort_order' => 1,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'ROLES'],
            [
                'name' => 'Roles',
                'icon' => 'user-cog',
                'route' => '/admin/roles',
                'permission_code' => 'roles.view',
                'parent_id' => $administration->id,
                'sort_order' => 2,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'PERMISSIONS'],
            [
                'name' => 'Permissions',
                'icon' => 'key-round',
                'route' => '/admin/permissions',
                'permission_code' => 'permissions.view',
                'parent_id' => $administration->id,
                'sort_order' => 3,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'MODULES'],
            [
                'name' => 'Modules',
                'icon' => 'menu',
                'route' => '/admin/modules',
                'permission_code' => 'modules.view',
                'parent_id' => $administration->id,
                'sort_order' => 4,
                'active' => true,
            ]
        );
        Module::updateOrCreate(
            ['code' => 'CUSTOMERS'],
            [
                'name' => 'Customers',
                'icon' => 'users',
                'route' => '/admin/customers',
                'permission_code' => 'customers.view',
                'parent_id' => $administration->id,
                'sort_order' => 5,
                'active' => true,
            ]
        );
         Module::updateOrCreate(
            ['code' => 'MERCHANT'],
            [
                'name' => 'Merchant',
                'icon' => 'users',
                'route' => '/admin/merchant',
                'permission_code' => 'merchants.view',
                'parent_id' => $administration->id,
                'sort_order' => 6,
                'active' => true,
            ]
        );
    }
}