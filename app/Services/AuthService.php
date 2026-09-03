<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(array $credentials, Request $request): array
    {
        $user = User::with([
            'role',
            'merchant',
        ])
            ->where('email', $credentials['email'])
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => [
                    'Invalid email or password.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Check if account is locked
        |--------------------------------------------------------------------------
        */

        if (
            $user->is_lock ||
            $user->status === User::STATUS_LOCKED
        ) {
            throw ValidationException::withMessages([
                'email' => [
                    'Your account has been locked. Please contact the administrator.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify password
        |--------------------------------------------------------------------------
        */

        if (! Hash::check(
            $credentials['password'],
            $user->password
        )) {
            $user->increment('login_attempt');

            if ($user->login_attempt >= 5) {
                $user->update([
                    'status' => User::STATUS_LOCKED,
                    'is_lock' => true,
                ]);
            }

            throw ValidationException::withMessages([
                'email' => [
                    'Invalid email or password.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Check account status
        |--------------------------------------------------------------------------
        */

        if ($user->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => [
                    'Your account is inactive.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Employer Merchant Validation
        |--------------------------------------------------------------------------
        |
        | Every EMPLOYER account must belong to an active Merchant.
        |
        */

        if ($user->role?->code === 'EMPLOYER') {

            if (! $user->merchant_id) {
                throw ValidationException::withMessages([
                    'email' => [
                        'This Employer account is not associated with a Merchant.',
                    ],
                ]);
            }

            if (
                ! $user->merchant ||
                $user->merchant->status !== Merchant::STATUS_ACTIVE
            ) {
                throw ValidationException::withMessages([
                    'email' => [
                        'The Merchant associated with this Employer account is not active.',
                    ],
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */

        $user->update([
            'login_attempt' => 0,
            'is_login' => true,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Access Token
        |--------------------------------------------------------------------------
        */

        $token = $user
            ->createToken('auth_token')
            ->plainTextToken;

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'must_change_password' => (bool) $user->must_change_password,
        ];
    }

    public function logout($user): void
    {
        $user->update([
            'is_login' => false,
        ]);

        $user->currentAccessToken()?->delete();
    }

    public function changePassword(User $user, string $currentPassword, string $newPassword ): void {
        /*
        |--------------------------------------------------------------------------
        | Verify current password
        |--------------------------------------------------------------------------
        */

        if (! Hash::check($currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'currentPassword' => [
                    'The current password is incorrect.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent reusing the same password
        |--------------------------------------------------------------------------
        */

        if (Hash::check($newPassword, $user->password)) {
            throw ValidationException::withMessages([
                'newPassword' => [
                    'The new password must be different from your current password.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update password
        |--------------------------------------------------------------------------
        |
        | User model already casts password as "hashed", so we pass the
        | plain new password and Laravel will hash it.
        |
        */

        $user->update([
            'password' => $newPassword,
            'must_change_password' => false,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Invalidate current access token
        |--------------------------------------------------------------------------
        */

        $user->currentAccessToken()?->delete();
    }
}