<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {
    }

    /**
     * Logout the authenticated user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(
            null,
            'Logout successful.'
        );
    }
}