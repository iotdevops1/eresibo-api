<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Payroll\PayrollBatchFilterRequest;
use App\Http\Requests\Payroll\StorePayrollBatchRequest;
use App\Http\Requests\Payroll\UpdatePayrollBatchRequest;
use App\Http\Resources\PayrollBatchCollection;
use App\Http\Resources\PayrollBatchResource;
use App\Services\Payroll\PayrollBatchService;
use Illuminate\Http\Request;

class PayrollBatchController extends BaseApiController
{
    public function __construct(
        protected PayrollBatchService $payrollBatchService
    ) {
    }

    /**
     * List payroll batches for the authenticated employer.
     */
    public function index(PayrollBatchFilterRequest $request)
    {
        $user = $request->user();

        $batches = $this->payrollBatchService->index(
            $user->id,
            $request->validated()
        );

        return $this->success(
            new PayrollBatchCollection($batches),
            'Payroll batches retrieved successfully.'
        );
    }

    /**
     * Create a draft payroll batch.
     */
    public function store(StorePayrollBatchRequest $request)
    {
        $user = $request->user();

        $batch = $this->payrollBatchService->store(
            $user->id,
            $request->validated()
        );

        return $this->success(
            new PayrollBatchResource($batch),
            'Payroll batch created successfully.',
            201
        );
    }

    /**
     * View a payroll batch.
     */
    public function show(Request $request,string $uuid) {
        $user = $request->user();

        $batch = $this->payrollBatchService->show(
            $uuid,
            $user->id
        );

        return $this->success(
            new PayrollBatchResource($batch),
            'Payroll batch retrieved successfully.'
        );
    }

    /**
     * Update a draft payroll batch.
     */
    public function update(UpdatePayrollBatchRequest $request, string $uuid) {
        $user = $request->user();

        $batch = $this->payrollBatchService->show(
            $uuid,
            $user->id
        );

        $batch = $this->payrollBatchService->update(
            $batch,
            $request->validated()
        );

        return $this->success(
            new PayrollBatchResource($batch),
            'Payroll batch updated successfully.'
        );
    }

    public function submit(Request $request,string $uuid) {
        $user = $request->user();

        $batch = $this->payrollBatchService->show(
            $uuid,
            $user->id
        );

        $batch = $this->payrollBatchService->submit(
            $batch
        );

        return $this->success(
            new PayrollBatchResource($batch),
            'Payroll batch submitted successfully.'
        );
    }
}