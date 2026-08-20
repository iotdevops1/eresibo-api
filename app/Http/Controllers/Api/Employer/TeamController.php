<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Employee\EmployeeFilterRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeCollection;
use App\Http\Resources\EmployeeResource;
use App\Services\Employee\EmployeeService;
use Illuminate\Http\Request;

class TeamController extends BaseApiController
{
    public function __construct(
        protected EmployeeService $employeeService
    ) {
    }

    public function index(EmployeeFilterRequest $request)
    {
        $user = $request->user();

        $employees = $this->employeeService->index(
            $user->id,
            $request->validated()
        );

        return $this->success(
            new EmployeeCollection($employees),
            'Team members retrieved successfully.'
        );
    }

    public function store(StoreEmployeeRequest $request)
    {
        $user = $request->user();

        $employee = $this->employeeService->store(
            $user->id,
            $request->validated()
        );

        return $this->success(
            new EmployeeResource($employee),
            'Team member created successfully.',
            201
        );
    }

    public function show(Request $request, string $uuid)
    {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->id
        );

        return $this->success(
            new EmployeeResource($employee),
            'Team member retrieved successfully.'
        );
    }

    public function update(UpdateEmployeeRequest $request, string $uuid) {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->id
        );

        $employee = $this->employeeService->update(
            $employee,
            $request->validated()
        );

        return $this->success(
            new EmployeeResource($employee),
            'Team member updated successfully.'
        );
    }

    public function destroy(Request $request, string $uuid)
    {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->id
        );

        $this->employeeService->destroy($employee);

        return $this->success(
            null,
            'Team member deleted successfully.'
        );
    }
}