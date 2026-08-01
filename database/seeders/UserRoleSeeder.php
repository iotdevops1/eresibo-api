<?php

namespace Database\Seeders;

use App\Models\UserRole;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        UserRole::insert([
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'code' => 'SUPER_ADMIN',
                'name' => 'Super Administrator',
                'description' => 'Has full access to the system.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'code' => 'ADMIN',
                'name' => 'Administrator',
                'description' => 'Can manage customers and receipts.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'code' => 'CUSTOMER',
                'name' => 'Customer',
                'description' => 'Regular customer account.',
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}