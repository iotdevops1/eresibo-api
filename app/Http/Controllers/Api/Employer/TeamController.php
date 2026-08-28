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

    /**
     * List all employees belonging to the authenticated
     * Employer's Merchant.
     */
    public function index(EmployeeFilterRequest $request)
    {
        $user = $request->user();

        $employees = $this->employeeService->index(
            $user->merchant_id,
            $request->validated()
        );

        return $this->success(
            new EmployeeCollection($employees),
            'Team members retrieved successfully.'
        );
    }

    /**
     * Create an employee under the authenticated
     * Employer's Merchant.
     */
    public function store(StoreEmployeeRequest $request)
    {
        $user = $request->user();

        $employee = $this->employeeService->store(
            $user->merchant_id,
            $request->validated()
        );

        return $this->success(
            new EmployeeResource($employee),
            'Team member created successfully.',
            201
        );
    }

    /**
     * View an employee belonging to the authenticated
     * Employer's Merchant.
     */
    public function show(
        Request $request,
        string $uuid
    ) {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->merchant_id
        );

        return $this->success(
            new EmployeeResource($employee),
            'Team member retrieved successfully.'
        );
    }

    /**
     * Update an employee belonging to the authenticated
     * Employer's Merchant.
     */
    public function update(
        UpdateEmployeeRequest $request,
        string $uuid
    ) {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->merchant_id
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

    /**
     * Soft delete an employee belonging to the authenticated
     * Employer's Merchant.
     */
    public function destroy(
        Request $request,
        string $uuid
    ) {
        $user = $request->user();

        $employee = $this->employeeService->show(
            $uuid,
            $user->merchant_id
        );

        $this->employeeService->destroy(
            $employee
        );

        return $this->success(
            null,
            'Team member deleted successfully.'
        );
    }
}