<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Merchant\MerchantFilterRequest;
use App\Http\Requests\Merchant\StoreMerchantRequest;
use App\Http\Requests\Merchant\UpdateMerchantRequest;
use App\Http\Resources\MerchantCollection;
use App\Http\Resources\MerchantResource;
use App\Services\Merchant\MerchantService;
use Illuminate\Http\Request;

class MerchantController extends BaseApiController
{
    public function __construct(
        protected MerchantService $merchantService
    ) {
    }

    /**
     * List merchants.
     */
    public function index(MerchantFilterRequest $request)
    {
        $merchants = $this->merchantService->index(
            $request->validated()
        );

        return $this->success(
            new MerchantCollection($merchants),
            'Merchants retrieved successfully.'
        );
    }

    /**
     * Create merchant.
     */
    public function store(StoreMerchantRequest $request)
    {
        $merchant = $this->merchantService->store(
            $request->validated()
        );

        return $this->success(
            new MerchantResource($merchant),
            'Merchant created successfully.',
            201
        );
    }

    /**
     * View merchant.
     */
    public function show(string $uuid)
    {
        $merchant = $this->merchantService->show($uuid);

        return $this->success(
            new MerchantResource($merchant),
            'Merchant retrieved successfully.'
        );
    }

    /**
     * Update merchant.
     */
    public function update(UpdateMerchantRequest $request, string $uuid) {
        $merchant = $this->merchantService->show($uuid);

        $merchant = $this->merchantService->update(
            $merchant,
            $request->validated()
        );

        return $this->success(
            new MerchantResource($merchant),
            'Merchant updated successfully.'
        );
    }

    /**
     * Delete merchant.
     */
    public function destroy(string $uuid)
    {
        $merchant = $this->merchantService->show($uuid);

        $this->merchantService->destroy($merchant);

        return $this->success(
            null,
            'Merchant deleted successfully.'
        );
    }
}