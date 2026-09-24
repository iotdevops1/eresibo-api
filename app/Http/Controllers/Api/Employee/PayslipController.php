<?php
namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Payslip\EmployeePayslipFilterRequest;
use App\Http\Resources\PayslipCollection;
use App\Http\Resources\PayslipResource;
use App\Services\Payslip\EmployeePayslipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayslipController extends BaseApiController {
    public function __construct(protected EmployeePayslipService $employeePayslipService) {}

    public function index(EmployeePayslipFilterRequest $request): JsonResponse {
        return $this->success(
            new PayslipCollection($this->employeePayslipService->index($request->user()->id,$request->validated())),
            'Employee payslips retrieved successfully.'
        );
    }

    public function show(Request $request,string $uuid): JsonResponse {
        return $this->success(
            new PayslipResource($this->employeePayslipService->show($uuid,$request->user()->id)),
            'Employee payslip retrieved successfully.'
        );
    }
}
