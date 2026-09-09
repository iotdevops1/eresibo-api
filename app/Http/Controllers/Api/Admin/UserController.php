<?php

namespace App\Http\Controllers\Api\Admin;


use App\Models\User;
use App\Http\Controllers\BaseApiController;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\User\UserService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class UserController extends BaseApiController
{
   public function __construct(
        protected UserService $userService
    ) {
    }

    public function index(UserFilterRequest $request)
    {
        $users = $this->userService->index(
            $request->validated()
        );

        return $this->success(
            new UserCollection($users),
            'Users retrieved successfully.'
        );
    }

    public function store(StoreUserRequest $request)
    {
        $user = $this->userService->store(
            $request->validated()
        );

        return $this->success(
            new UserResource($user),
            'User created successfully.',
            201
        );
    }

    public function show(string $uuid)
    {
        $user = $this->userService->show($uuid);

        return $this->success(
            new UserResource($user),
            'User retrieved successfully.'
        );
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $user = $this->userService->update(
            $user,
            $request->validated()
        );

        return $this->success(
            new UserResource($user),
            'User updated successfully.'
        );
    }

    public function destroy(User $user)
    {
        $this->userService->destroy($user);

        return $this->success(
            null,
            'User deleted successfully.'
        );
    }

    public function generateTemporaryPassword(
        \Illuminate\Http\Request $request,
        string $userUuid
    ): JsonResponse {
        $actor = $request->user();

        $targetUser = User::query()
            ->where('uuid', $userUuid)
            ->first();

        if (! $targetUser) {
            throw ValidationException::withMessages([
                'user' => [
                    'User not found.',
                ],
            ]);
        }

        $actorRole = $actor->role?->code;
        $targetRole = $targetUser->role?->code;

        /*
        |--------------------------------------------------------------------------
        | Allowed actors
        |--------------------------------------------------------------------------
        */

        if (! in_array($actorRole, [
            'SUPER_ADMIN',
            'ADMIN',
            'EMPLOYER',
        ], true)) {
            throw ValidationException::withMessages([
                'authorization' => [
                    'You are not authorized to generate a temporary password.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent lower-level users from resetting SUPER_ADMIN
        |--------------------------------------------------------------------------
        */

        if (
            $targetRole === 'SUPER_ADMIN'
            && $actorRole !== 'SUPER_ADMIN'
        ) {
            throw ValidationException::withMessages([
                'authorization' => [
                    'You are not authorized to generate a temporary password for a SUPER_ADMIN.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | EMPLOYER restrictions
        |--------------------------------------------------------------------------
        */

        if ($actorRole === 'EMPLOYER') {

            // Employer can only reset EMPLOYEE accounts.
            if ($targetRole !== 'EMPLOYEE') {
                throw ValidationException::withMessages([
                    'authorization' => [
                        'Employers can only generate temporary passwords for employees.',
                    ],
                ]);
            }

            // Target must have an employee record.
            $targetEmployee = $targetUser->employee;

            if (! $targetEmployee) {
                throw ValidationException::withMessages([
                    'authorization' => [
                        'Employee record not found.',
                    ],
                ]);
            }

            // Employer and employee must belong to the same merchant.
            if (
                (int) $targetEmployee->merchant_id !==
                (int) $actor->merchant_id
            ) {
                throw ValidationException::withMessages([
                    'authorization' => [
                        'You can only generate temporary passwords for employees under your merchant.',
                    ],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Generate temporary password
        |--------------------------------------------------------------------------
        */

        $temporaryPassword =
            \Illuminate\Support\Str::upper(
                \Illuminate\Support\Str::random(4)
            )
            . random_int(1000, 9999)
            . \Illuminate\Support\Str::upper(
                \Illuminate\Support\Str::random(4)
            );

        /*
        |--------------------------------------------------------------------------
        | Update credentials
        |--------------------------------------------------------------------------
        */

        \Illuminate\Support\Facades\DB::transaction(function () use (
            $targetUser,
            $temporaryPassword
        ) {
            $targetUser->update([
                'password' => \Illuminate\Support\Facades\Hash::make(
                    $temporaryPassword
                ),
                'must_change_password' => true,
            ]);

            // Revoke all active Sanctum tokens.
            $targetUser->tokens()->delete();
        });

        return $this->success(
            [
                'user_uuid' => $targetUser->uuid,
                'temporary_password' => $temporaryPassword,
                'must_change_password' => true,
            ],
            'Temporary password generated successfully.'
        );
    }
} 
