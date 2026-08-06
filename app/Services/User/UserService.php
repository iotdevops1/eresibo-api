<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserRole;
use App\Filters\UserFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function index(array $filters)
    {
        $query = User::query()
            ->with('role');

        $filter = new UserFilter($filters);

        return $filter
            ->apply($query)
            ->paginate(
                $filter->perPage()
            );
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $role = UserRole::where(
                'code',
                $data['role_code']
            )->firstOrFail();

            $user = User::create([
                'role_id'       => $role->id,
                'name'          => $data['name'],
                'email'         => strtolower($data['email']),
                'mobile'        => $data['mobile'],
                'password'      => Hash::make($data['password']),
                'status'        => $data['status'],
                'is_login'      => false,
                'login_attempt' => 0,
                'is_lock'       => false,
            ]);

            return $user->load('role');
        });
    }

    public function show(string $uuid)
    {
    }

    public function update(string $uuid, array $data)
    {
    }

    public function destroy(string $uuid)
    {
    }
}