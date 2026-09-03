<?php

namespace App\Services\Merchant;

use App\Models\Merchant;
use App\Models\User;
use App\Models\UserRole;
use App\Repositories\Merchant\EmployerRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmployerService
{
    public function __construct(
        protected EmployerRepository $employerRepository
    ) {
    }

    public function index(
        int $merchantId,
        array $filters = []
    ) {
        return $this->employerRepository->paginateByMerchant(
            $merchantId,
            $filters
        );
    }

    public function show(
        string $uuid,
        int $merchantId
    ): User {
        $employer = $this->employerRepository->findByUuid(
            $uuid,
            $merchantId
        );

        if (! $employer) {
            throw ValidationException::withMessages([
                'employer' => [
                    'Employer not found.'
                ],
            ]);
        }

        return $employer;
    }

    public function store(
        int $merchantId,
        array $data
    ): User {
        /*
        |--------------------------------------------------------------------------
        | Validate Merchant
        |--------------------------------------------------------------------------
        */

        $merchant = Merchant::query()
            ->where('id', $merchantId)
            ->where('status', Merchant::STATUS_ACTIVE)
            ->first();

        if (! $merchant) {
            throw ValidationException::withMessages([
                'merchant' => [
                    'Merchant does not exist or is not active.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Employer Role
        |--------------------------------------------------------------------------
        */

        $role = UserRole::query()
            ->where('code', 'EMPLOYER')
            ->first();

        if (! $role) {
            throw ValidationException::withMessages([
                'role' => [
                    'EMPLOYER role is not configured.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Email Uniqueness
        |--------------------------------------------------------------------------
        */

        $email = strtolower(trim($data['email']));

        if (
            User::query()
                ->where('email', $email)
                ->exists()
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'The email has already been taken.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Create Employer
        |--------------------------------------------------------------------------
        */

        $employer = User::create([
            'merchant_id' => $merchant->id,

            'role_id' => $role->id,

            'name' => $data['name'],

            'email' => $email,

            'mobile' => $data['mobile'] ?? null,

            'password' => Hash::make(
                $data['password']
            ),

            'status' => $data['status'],

            'is_login' => false,

            'login_attempt' => 0,

            'is_lock' => false,
        ]);

        return $employer->load([
            'role',
            'merchant',
        ]);
    }

    public function update(
        User $employer,
        array $data
    ): User {
        $updateData = collect($data)
            ->only([
                'name',
                'email',
                'mobile',
                'status',
            ])
            ->toArray();

        if (isset($data['password'])) {
            $updateData['password'] = Hash::make(
                $data['password']
            );
        }

        $employer->update($updateData);

        return $employer->refresh()->load([
            'role',
            'merchant',
        ]);
    }

    public function destroy(
        User $employer
    ): void {
        if ($employer->is_login) {
            throw ValidationException::withMessages([
                'employer' => [
                    'Please log out the employer before deleting the account.'
                ],
            ]);
        }

        $employer->update([
            'status' => User::STATUS_INACTIVE,
        ]);

        $employer->delete();
    }
}