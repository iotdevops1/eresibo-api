<?php
namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\BaseApiController;
use App\Http\Resources\WalletBalanceResource;
use App\Services\Wallet\WalletBalanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends BaseApiController {
    public function __construct(protected WalletBalanceService $walletBalanceService) {}

    public function show(Request $request): JsonResponse {
        return $this->success(
            new WalletBalanceResource($this->walletBalanceService->employee($request->user())),
            'Employee wallet balance retrieved successfully.'
        );
    }
}
