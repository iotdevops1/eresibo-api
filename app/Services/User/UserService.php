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

    public function show(string $uuid): User
    {
        $user = User::with('role')
            ->where('uuid', $uuid)
            ->first();

        if (! $user) {
            abort(404, 'User not found.');
        }

        return $user;
    }

    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            $role = UserRole::where(
                'code',
                $data['role_code']
            )->firstOrFail();

            $user->update([
                'role_id' => $role->id,
                'name'    => $data['name'],
                'email'   => strtolower($data['email']),
                'mobile'  => $data['mobile'],
                'status'  => $data['status'],
            ]);

            return $user->fresh()->load('role');
        });
    }

    public function destroy(User $user): void
    {
        DB::transaction(function () use ($user) {

        
            if ($user->id === auth()->id()) {
                throw ValidationException::withMessages([
                    'user' => [
                        'You cannot delete your own account.'
                    ]
                ]);
            }

           
            if ($user->role?->code === 'SUPER_ADMIN') {

                $remainingSuperAdmins = User::query()
                    ->whereHas('role', function ($query) {
                        $query->where('code', 'SUPER_ADMIN');
                    })
                    ->count();

                if ($remainingSuperAdmins <= 1) {
                    throw ValidationException::withMessages([
                        'user' => [
                            'At least one SUPER_ADMIN must remain in the system.'
                        ]
                    ]);
                }
            }

            $user->delete();
        });
    }
}