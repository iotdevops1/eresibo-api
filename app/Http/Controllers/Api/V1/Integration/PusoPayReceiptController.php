<?php

namespace App\Http\Controllers\Api\V1\Integration;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Integration\StorePusoPayReceiptRequest;
use App\Http\Resources\PusoPayReceiptResource;
use App\Services\Integration\PusoPayReceiptService;
use Illuminate\Http\JsonResponse;

class PusoPayReceiptController extends BaseApiController
{
    public function __construct(
        protected PusoPayReceiptService $pusoPayReceiptService
    ) {
    }

    public function store(
        StorePusoPayReceiptRequest $request
    ): JsonResponse {
        $receipt = $this->pusoPayReceiptService
            ->createOrGet(
                $request->validated()
            );

        return $this->success(
            new PusoPayReceiptResource($receipt),
            'Receipt created successfully.',
            201
        );
    }
}