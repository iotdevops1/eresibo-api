<?php

namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Dispute\DisputeFilterRequest;
use App\Http\Requests\Dispute\StoreDisputeMessageRequest;
use App\Http\Requests\Dispute\StoreEmployerDisputeRequest;
use App\Http\Requests\Dispute\UpdateDisputeRequest;
use App\Http\Resources\DisputeCollection;
use App\Http\Resources\DisputeResource;
use App\Services\Dispute\DisputeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisputeController extends BaseApiController
{
    public function __construct(protected DisputeService $disputeService)
    {
    }

    public function index(DisputeFilterRequest $request): JsonResponse
    {
        return $this->success(
            new DisputeCollection(
                $this->disputeService->employerIndex($request->user(), $request->validated())
            ),
            'Disputes retrieved successfully.'
        );
    }

    public function store(StoreEmployerDisputeRequest $request): JsonResponse
    {
        return $this->success(
            new DisputeResource(
                $this->disputeService->employerStore($request->user(), $request->validated())
            ),
            'Dispute created successfully.',
            201
        );
    }

    public function show(Request $request, string $uuid): JsonResponse
    {
        return $this->success(
            new DisputeResource($this->disputeService->employerShow($request->user(), $uuid)),
            'Dispute retrieved successfully.'
        );
    }

    public function update(UpdateDisputeRequest $request, string $uuid): JsonResponse
    {
        return $this->success(
            new DisputeResource(
                $this->disputeService->employerUpdate($request->user(), $uuid, $request->validated())
            ),
            'Dispute updated successfully.'
        );
    }

    public function addMessage(StoreDisputeMessageRequest $request, string $uuid): JsonResponse
    {
        return $this->success(
            new DisputeResource(
                $this->disputeService->employerAddMessage(
                    $request->user(),
                    $uuid,
                    $request->validated('message')
                )
            ),
            'Dispute message added successfully.'
        );
    }
}
