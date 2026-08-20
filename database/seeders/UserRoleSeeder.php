<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'code' => 'SUPER_ADMIN',
                'name' => 'Super Administrator',
                'description' => 'Has full access to the system.',
            ],
            [
                'code' => 'ADMIN',
                'name' => 'Administrator',
                'description' => 'Can manage customers and receipts.',
            ],
            [
                'code' => 'CUSTOMER',
                'name' => 'Customer',
                'description' => 'Regular customer account.',
            ],
        ];

        foreach ($roles as $role) {

            UserRole::updateOrCreate(
                [
                    'code' => $role['code'],
                ],
                [
                    'name' => $role['name'],
                    'description' => $role['description'],
                    'active' => true,
                ]
            );

        }
    }
}