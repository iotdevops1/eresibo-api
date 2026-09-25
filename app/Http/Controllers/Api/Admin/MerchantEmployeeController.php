<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Merchant\MerchantEmployeeFilterRequest;
use App\Http\Resources\EmployeeCollection;
use App\Services\Merchant\MerchantEmployeeService;
use App\Services\Merchant\MerchantService;
use App\Services\Employee\EmployeeService;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeResource;

class MerchantEmployeeController extends BaseApiController
{
    public function __construct(
        protected MerchantService $merchantService,
        protected MerchantEmployeeService $merchantEmployeeService,
        protected EmployeeService $employeeService
    ) {
    }

    public function index(MerchantEmployeeFilterRequest $request,string $merchantUuid) {
        $merchant = $this->merchantService->show($merchantUuid);

        $employees = $this->merchantEmployeeService->index(
            $merchant->id,
            $request->validated()
        );

        return $this->success(
            new EmployeeCollection($employees),
            'Merchant employees retrieved successfully.'
        );
    }

    public function store(StoreEmployeeRequest $request, string $merchantUuid)
    {
        $merchant = $this->merchantService->show($merchantUuid);
        $employee = $this->employeeService->store($merchant->id, $request->validated());

        return $this->success(new EmployeeResource($employee), 'Employee created successfully.', 201);
    }

    public function update(UpdateEmployeeRequest $request, string $merchantUuid, string $uuid)
    {
        $merchant = $this->merchantService->show($merchantUuid);
        $employee = $this->employeeService->show($uuid, $merchant->id);
        $employee = $this->employeeService->update($employee, $request->validated());

        return $this->success(new EmployeeResource($employee), 'Employee updated successfully.');
    }
}
