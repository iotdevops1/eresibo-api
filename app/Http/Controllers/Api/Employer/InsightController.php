<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Insights\EmployerInsightsFilterRequest;
use App\Services\Insights\EmployerInsightsService;
use Illuminate\Http\JsonResponse;

class InsightController extends BaseApiController
{
    public function __construct(protected EmployerInsightsService $employerInsightsService)
    {
    }

    public function overview(EmployerInsightsFilterRequest $request): JsonResponse
    {
        return $this->success(
            $this->employerInsightsService->overview($request->user(), $request->validated()),
            'Insights overview retrieved successfully.'
        );
    }

    public function payrollDeepDive(EmployerInsightsFilterRequest $request): JsonResponse
    {
        return $this->success(
            $this->employerInsightsService->payrollDeepDive($request->user(), $request->validated()),
            'Payroll insights retrieved successfully.'
        );
    }

    public function payslips(EmployerInsightsFilterRequest $request): JsonResponse
    {
        return $this->success(
            $this->employerInsightsService->payslips($request->user(), $request->validated()),
            'Payslip insights retrieved successfully.'
        );
    }

    public function fundHolds(EmployerInsightsFilterRequest $request): JsonResponse
    {
        return $this->success(
            $this->employerInsightsService->fundHolds($request->user(), $request->validated()),
            'Fund hold insights retrieved successfully.'
        );
    }
}
