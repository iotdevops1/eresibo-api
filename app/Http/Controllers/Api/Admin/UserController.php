<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Requests\User\UserFilterRequest;
use App\Http\Resources\UserCollection;
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
}
