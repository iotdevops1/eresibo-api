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
        $username = trim($credentials['username']);

        $user = User::with([
            'role',
            'merchant',
        ])
            ->where(function ($query) use ($username) {
                if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
                    $query->where(
                        'email',
                        strtolower($username)
                    );
                } else {
                    $query->where(
                        'mobile',
                        $this->normalizeMobile($username)
                    );
                }
            })
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'username' => [
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
                'username' => [
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
                'username' => [
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
                'username' => [
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
                    'username' => [
                        'This Employer account is not associated with a Merchant.',
                    ],
                ]);
            }

            if (
                ! $user->merchant ||
                $user->merchant->status !== Merchant::STATUS_ACTIVE
            ) {
                throw ValidationException::withMessages([
                    'username' => [
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

    /**
     * Normalize Philippine mobile numbers.
     *
     * Accepted:
     * 09171234567
     * +639171234567
     * 639171234567
     *
     * Canonical format:
     * 09171234567
     */
    private function normalizeMobile(string $mobile): string
    {
        $mobile = preg_replace(
            '/[\s\-()]+/',
            '',
            trim($mobile)
        );

        if (str_starts_with($mobile, '+63')) {
            return '0' . substr($mobile, 3);
        }

        if (str_starts_with($mobile, '63')) {
            return '0' . substr($mobile, 2);
        }

        return $mobile;
    }

    public function logout($user): void
    {
        $user->update([
            'is_login' => false,
        ]);

        $user->currentAccessToken()?->delete();
    }

    public function changePassword(
        User $user,
        string $currentPassword,
        string $newPassword
    ): void {
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