<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $role = UserRole::where('code', 'SUPER_ADMIN')->first();

        User::updateOrCreate(
            [
                'email' => 'admin@eresibo.com',
            ],
            [
                'role_id'          => $role->id,
                'name'             => 'Super Administrator',
                'mobile'           => '09171234567',
                'password'         => Hash::make('Admin@123'),
                'status'           => 'ACTIVE',
                'is_login'         => false,
                'login_attempt'    => 0,
                'is_lock'          => false,
                'email_verified_at'=> now(),
            ]
        );
    }
}