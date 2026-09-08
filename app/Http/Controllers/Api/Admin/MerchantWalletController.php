<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Wallet\PrefundMerchantRequest;
use App\Models\Merchant;
use App\Models\Wallet;
use App\Services\Wallet\WalletService;
use App\Http\Resources\MerchantWalletResource;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MerchantWalletController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected WalletService $walletService
    ) {
    }

    public function prefund(
        PrefundMerchantRequest $request,
        string $merchantUuid
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Find Merchant
        |--------------------------------------------------------------------------
        */

        $merchant = Merchant::query()
            ->where('uuid', $merchantUuid)
            ->first();

        if (! $merchant) {
            throw ValidationException::withMessages([
                'merchant' => [
                    'Merchant not found.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Validate amount consistency
        |--------------------------------------------------------------------------
        */
        $amountMinorUnits = (int) $request->validated(
            'amount_minor_units'
        );
        
        /*
        |--------------------------------------------------------------------------
        | Get eResibo Main Wallet
        |--------------------------------------------------------------------------
        */

        $mainWallet = Wallet::query()
            ->where(
                'owner_type',
                Wallet::OWNER_TYPE_MAIN
            )
            ->where('owner_id', 1)
            ->where(
                'currency',
                $request->validated('currency')
            )
            ->where(
                'status',
                Wallet::STATUS_ACTIVE
            )
            ->first();

        if (! $mainWallet) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'eResibo Main Wallet not found.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Merchant Wallet
        |--------------------------------------------------------------------------
        */

        $merchantWallet = Wallet::query()
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

        if (! $merchantWallet) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'Merchant wallet not found.',
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Prefund Merchant
        |--------------------------------------------------------------------------
        */

        $transaction = $this->walletService->prefundMerchant(
            $mainWallet,
            $merchantWallet,
            $amountMinorUnits,
            $request->validated('reference'),
            $request->validated('description'),
            [
                'merchant_uuid' => $merchant->uuid,
                'merchant_code' => $merchant->merchant_code,
                'initiated_by_user_id' => $request->user()->id,
            ]
        );

        return $this->success(
            $transaction->load('entries'),
            'Merchant wallet prefunded successfully.',
            201
        );
    }

    public function merchants(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $wallets = Wallet::query()
            ->where(
                'owner_type',
                Wallet::OWNER_TYPE_MERCHANT
            )
            ->where('currency', 'PHP')
            ->with('merchant')
            ->paginate($perPage);

        return $this->success(
            MerchantWalletResource::collection($wallets),
            'Merchant wallets retrieved successfully.'
        );
    }
}