<?php
namespace App\Http\Controllers\Api\Employer;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\WalletBalanceResource;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends BaseApiController {
    public function __construct(protected WalletBalanceService $walletBalanceService) {}

    public function employer(Request $request): JsonResponse {
        return $this->success(
            new WalletBalanceResource($this->walletBalanceService->employer($request->user())),
            'Employer wallet balance retrieved successfully.'
        );
    }

    public function merchant(Request $request): JsonResponse {
        return $this->success(
            new WalletBalanceResource($this->walletBalanceService->merchant($request->user())),
            'Merchant wallet balance retrieved successfully.'
        );
    }
}
