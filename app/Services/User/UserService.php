<?php

namespace App\Services\User;

use App\Models\User;
use App\Models\UserRole;
use App\Repositories\User\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ) {
    }

    public function index(array $filters)
    {
        return $this->userRepository->paginate($filters);
    }

    public function store(array $data): User
    {
        return DB::transaction(function () use ($data) {

            $role = UserRole::where(
                'code',
                $data['role_code']
            )->firstOrFail();

            $user = $this->userRepository->create([
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
        $user = $this->userRepository->findByUuid($uuid);

        if (! $user) {
            abort(404, 'User not found.');
        }

        return $user;
    }

    public function update(User $user, array $data ): User {
        return DB::transaction(function () use ($user, $data) {

            $role = UserRole::where(
                'code',
                $data['role_code']
            )->firstOrFail();

            $updatedUser = $this->userRepository->update(
                $user,
                [
                    'role_id' => $role->id,
                    'name'    => $data['name'],
                    'email'   => strtolower($data['email']),
                    'mobile'  => $data['mobile'],
                    'status'  => $data['status'],
                ]
            );

            return $updatedUser->load('role');
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

                $superAdminCount = User::query()
                    ->whereHas('role', function ($query) {
                        $query->where('code', 'SUPER_ADMIN');
                    })
                    ->count();

                if ($superAdminCount <= 1) {
                    throw ValidationException::withMessages([
                        'user' => [
                            'At least one SUPER_ADMIN must remain in the system.'
                        ]
                    ]);
                }
            }

            $this->userRepository->delete($user);
        });
    }
}