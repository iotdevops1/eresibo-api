<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\UserResource;
use App\Http\Resources\LoginResource;

class AuthService
{
    public function login(array $credentials, Request $request): array
    {
        $user = User::with('role')
            ->where('email', $credentials['email'])
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Check if account is locked
        |--------------------------------------------------------------------------
        */
        if ($user->is_lock || $user->status === User::STATUS_LOCKED) {
            throw ValidationException::withMessages([
                'email' => ['Your account has been locked. Please contact the administrator.'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Verify password
        |--------------------------------------------------------------------------
        */
        if (! Hash::check($credentials['password'], $user->password)) {

            $user->increment('login_attempt');

            if ($user->login_attempt >= 5) {
                $user->update([
                    'status'  => User::STATUS_LOCKED,
                    'is_lock' => true,
                ]);
            }

            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Check account status
        |--------------------------------------------------------------------------
        */
        if ($user->status !== User::STATUS_ACTIVE) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive.'],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Successful Login
        |--------------------------------------------------------------------------
        */

        $user->update([
            'login_attempt' => 0,
            'is_login'      => true,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user,
        ];
    }

    public function logout($user): void
    {
        $user->update([
            'is_login' => false,
        ]);
        $user->currentAccessToken()?->delete();
    }

}