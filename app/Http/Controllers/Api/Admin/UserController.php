<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\User\UserService;

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
}
