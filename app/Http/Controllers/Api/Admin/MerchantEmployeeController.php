<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Merchant\MerchantEmployeeFilterRequest;
use App\Http\Resources\EmployeeCollection;
use App\Services\Merchant\MerchantEmployeeService;
use App\Services\Merchant\MerchantService;

class MerchantEmployeeController extends BaseApiController
{
    public function __construct(
        protected MerchantService $merchantService,
        protected MerchantEmployeeService $merchantEmployeeService
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
}