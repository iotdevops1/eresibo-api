<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Merchant\EmployerFilterRequest;
use App\Http\Requests\Merchant\StoreEmployerRequest;
use App\Http\Requests\Merchant\UpdateEmployerRequest;
use App\Http\Resources\EmployerCollection;
use App\Http\Resources\EmployerResource;
use App\Services\Merchant\EmployerService;
use App\Services\Merchant\MerchantService;
use Illuminate\Http\Request;

class MerchantEmployerController extends BaseApiController
{
    public function __construct(
        protected MerchantService $merchantService,
        protected EmployerService $employerService
    ) {
    }

    public function index(EmployerFilterRequest $request, string $merchantUuid) {

        $merchant = $this->merchantService->show($merchantUuid);
        $employers = $this->employerService->index($merchant->id, $request->validated());

        return $this->success(
            new EmployerCollection($employers),
            'Merchant employers retrieved successfully.'
        );
    }

    public function store(StoreEmployerRequest $request, string $merchantUuid) {

        $merchant = $this->merchantService->show($merchantUuid);
        $employer = $this->employerService->store($merchant->id, $request->validated());

        return $this->success(
            new EmployerResource($employer),
            'Employer created successfully.',
            201
        );
    }

    public function show(Request $request, string $merchantUuid,string $userUuid) {

        $merchant = $this->merchantService->show($merchantUuid);
        $employer = $this->employerService->show($userUuid,$merchant->id);

        return $this->success(
            new EmployerResource($employer),
            'Employer retrieved successfully.'
        );
    }

    public function update(UpdateEmployerRequest $request,string $merchantUuid,string $userUuid) {

        $merchant = $this->merchantService->show($merchantUuid);
        $employer = $this->employerService->show($userUuid,$merchant->id);

        $employer = $this->employerService->update($employer, $request->validated());

        return $this->success(
            new EmployerResource($employer),
            'Employer updated successfully.'
        );
    }

    public function destroy(string $merchantUuid,string $userUuid) {

        $merchant = $this->merchantService->show($merchantUuid);
        $employer = $this->employerService->show($userUuid,$merchant->id);

        $this->employerService->destroy($employer);

        return $this->success(
            null,
            'Employer deleted successfully.'
        );
    }
}