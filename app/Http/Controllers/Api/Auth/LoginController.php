<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\AuthService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\LoginResource;

class LoginController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected AuthService $authService
    ) {}

    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {

            $login = $this->authService->login(
                $request->validated(),
                $request
            );

            return $this->success(
                new LoginResource($login),
                'Login successful.'
            );

        } catch (ValidationException $e) {

            return $this->error(
                'Login failed.',
                $e->errors(),
                422
            );
        }
    }
}