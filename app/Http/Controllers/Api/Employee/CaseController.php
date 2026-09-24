<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Dispute\DisputeFilterRequest;
use App\Http\Requests\Dispute\StoreDisputeMessageRequest;
use App\Http\Requests\Dispute\StoreEmployeeCaseRequest;
use App\Http\Resources\DisputeCollection;
use App\Http\Resources\DisputeResource;
use App\Services\Dispute\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CaseController extends BaseApiController
{
    public function __construct(protected DisputeService $disputeService)
    {
    }

    public function index(DisputeFilterRequest $request): JsonResponse
    {
        return $this->success(
            new DisputeCollection(
                $this->disputeService->employeeIndex($request->user(), $request->validated())
            ),
            'Employee cases retrieved successfully.'
        );
    }

    public function store(StoreEmployeeCaseRequest $request): JsonResponse
    {
        return $this->success(
            new DisputeResource(
                $this->disputeService->employeeStore($request->user(), $request->validated())
            ),
            'Case created successfully.',
            201
        );
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        return $this->success(
            new DisputeResource($this->disputeService->employeeShow($request->user(), $uuid)),
            'Employee case retrieved successfully.'
        );
    }

    public function addMessage(StoreDisputeMessageRequest $request, string $uuid): JsonResponse
    {
        return $this->success(
            new DisputeResource(
                $this->disputeService->employeeAddMessage(
                    $request->user(),
                    $uuid,
                    $request->validated('message')
                )
            ),
            'Case message added successfully.'
        );
    }
}
