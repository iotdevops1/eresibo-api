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
} 
