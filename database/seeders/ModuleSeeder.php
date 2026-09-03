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
                'is_menu' => true,
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
                'sort_order' => 11,
                'is_menu' => true,
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

        /*
        |--------------------------------------------------------------------------
        | Merchants
        |--------------------------------------------------------------------------
        */

        $merchants = Module::updateOrCreate(
            ['code' => 'MERCHANTS'],
            [
                'name' => 'Merchants',
                'icon' => 'store',
                'route' => null,
                'permission_code' => null,
                'parent_id' => null,
                'sort_order' => 100,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'MANAGEMENT'],
            [
                'name' => 'Management',
                'icon' => 'users',
                'route' => '/admin/management',
                'permission_code' => 'management.view',
                'parent_id' => $merchants->id,
                'sort_order' => 1,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'PREFUNDING'],
            [
                'name' => 'Prefunding',
                'icon' => 'wallet-cards',
                'route' => '/admin/prefunding',
                'permission_code' => 'prefunding.view',
                'parent_id' => $merchants->id,
                'sort_order' => 2,
                'is_menu' => true,
                'active' => true,
            ]
        );
        /*
        /*
        |--------------------------------------------------------------------------
        | Employer Sidebar
        |--------------------------------------------------------------------------
        */

        Module::updateOrCreate(
            ['code' => 'INSIGHTS'],
            [
                'name' => 'Insights',
                'icon' => 'chart-no-axes-combined',
                'route' => '/employer/insights',
                'permission_code' => 'insights.view',
                'sort_order' => 2,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'PAYROLL_BATCHES'],
            [
                'name' => 'Payroll batches',
                'icon' => 'layers',
                'route' => '/employer/payroll-batches',
                'permission_code' => 'payroll_batches.view',
                'sort_order' => 3,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'DISBURSEMENTS'],
            [
                'name' => 'Disbursements',
                'icon' => 'banknote',
                'route' => '/employer/disbursements',
                'permission_code' => 'disbursements.view',
                'sort_order' => 4,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'FUND_HOLDS'],
            [
                'name' => 'Fund holds',
                'icon' => 'lock',
                'route' => '/employer/fund-holds',
                'permission_code' => 'fund_holds.view',
                'sort_order' => 5,
                'is_menu' => true,
                'active' => true,
            ]
        );

        $payslips = Module::updateOrCreate(
            ['code' => 'EMPLOYER_PAYSLIPS'],
            [
                'name' => 'Payslips',
                'icon' => 'file-text',
                'route' => '/employer/payslips',
                'permission_code' => 'payslips.view',
                'sort_order' => 6,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'CREATE_PAYSLIPS'],
            [
                'name' => 'Create payslip',
                'icon' => 'file-plus-2',
                'route' => '/employer/payslips/create',
                'permission_code' => 'payslips.create',
                'parent_id' => $payslips->id,
                'sort_order' => 1,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'TEAM_DIRECTORY'],
            [
                'name' => 'Team directory',
                'icon' => 'users',
                'route' => '/employer/team-directory',
                'permission_code' => 'team.view',
                'sort_order' => 7,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'DISPUTES'],
            [
                'name' => 'Disputes',
                'icon' => 'message-circle-warning',
                'route' => '/employer/disputes',
                'permission_code' => 'disputes.view',
                'sort_order' => 8,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'EMPLOYER_REPORTS'],
            [
                'name' => 'Reports',
                'icon' => 'bar-chart-3',
                'route' => '/employer/reports',
                'permission_code' => 'reports.view',
                'sort_order' => 9,
                'is_menu' => true,
                'active' => true,
            ]
        );

        Module::updateOrCreate(
            ['code' => 'AUDIT_LOG'],
            [
                'name' => 'Audit log',
                'icon' => 'history',
                'route' => '/employer/audit-logs',
                'permission_code' => 'audit_logs.view',
                'sort_order' => 10,
                'is_menu' => true,
                'active' => true,
            ]
        );
    }
}