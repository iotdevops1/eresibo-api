<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ChangePasswordController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {
    }

    public function __invoke(
        ChangePasswordRequest $request
    ): JsonResponse {
        $user = $request->user();

        $this->authService->changePassword(
            $user,
            $request->validated('currentPassword'),
            $request->validated('newPassword')
        );

        return $this->success(
            null,
            'Password changed successfully.'
        );
    }
}