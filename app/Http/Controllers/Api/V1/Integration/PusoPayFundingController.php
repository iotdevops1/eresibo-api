<?php

namespace App\Http\Controllers\Api\V1\Integration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\PusoPayFundingRequest;
use App\Models\Merchant;
use App\Models\Wallet;
use App\Services\Wallet\Integrations\PusoPayFundingAdapter;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class PusoPayFundingController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected PusoPayFundingAdapter $adapter
    ) {
    }

    public function confirm(
        PusoPayFundingRequest $request
    ): JsonResponse {
        $merchant = Merchant::query()
            ->where(
                'merchant_code',
                $request->validated('merchant_code')
            )
            ->first();

        if (! $merchant) {
            throw ValidationException::withMessages([
                'merchant_code' => [
                    'Merchant not found.',
                ],
            ]);
        }

        $wallet = Wallet::query()
            ->where(
                'owner_type',
                Wallet::OWNER_TYPE_MERCHANT
            )
            ->where(
                'owner_id',
                $merchant->id
            )
            ->where(
                'currency',
                $request->validated('currency')
            )
            ->where(
                'status',
                Wallet::STATUS_ACTIVE
            )
            ->first();

        if (! $wallet) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'Merchant wallet not found.',
                ],
            ]);
        }

        $funding = $this->adapter->confirmFunding(
            merchantWallet: $wallet,
            externalReference: $request->validated(
                'externalReference'
            ),
            amountMinorUnits: (int) $request->validated(
                'amountMinorUnits'
            ),
            currency: $request->validated('currency'),
            metadata: [
                'merchant_code' => $merchant->merchant_code,
            ],
        );

        return $this->success(
            $funding,
            'PusoPay funding confirmed successfully.',
            200
        );
    }
}