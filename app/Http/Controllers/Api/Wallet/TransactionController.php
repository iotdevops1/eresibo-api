<?php

namespace App\Http\Controllers\Api\Wallet;

use App\Http\Controllers\BaseApiController;
use App\Http\Requests\Wallet\TransactionFilterRequest;
use App\Http\Resources\WalletTransactionCollection;
use App\Http\Resources\WalletTransactionResource;
use App\Services\Wallet\WalletTransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends BaseApiController
{
    public function __construct(
        protected WalletTransactionService $walletTransactionService,
    ) {
    }

    public function employeeIndex(TransactionFilterRequest $request): JsonResponse
    {
        return $this->success(
            new WalletTransactionCollection(
                $this->walletTransactionService->employeeIndex(
                    $request->user(),
                    $request->validated()
                )
            ),
            'Employee wallet transactions retrieved successfully.'
        );
    }

    public function employeeShow(Request $request, string $uuid): JsonResponse
    {
        return $this->success(
            new WalletTransactionResource(
                $this->walletTransactionService->employeeShow($request->user(), $uuid)
            ),
            'Employee wallet transaction retrieved successfully.'
        );
    }

    public function employerIndex(TransactionFilterRequest $request): JsonResponse
    {
        return $this->success(
            new WalletTransactionCollection(
                $this->walletTransactionService->employerIndex(
                    $request->user(),
                    $request->validated()
                )
            ),
            'Employer wallet transactions retrieved successfully.'
        );
    }

    public function employerShow(Request $request, string $uuid): JsonResponse
    {
        return $this->success(
            new WalletTransactionResource(
                $this->walletTransactionService->employerShow($request->user(), $uuid)
            ),
            'Employer wallet transaction retrieved successfully.'
        );
    }

    public function merchantIndex(TransactionFilterRequest $request): JsonResponse
    {
        return $this->success(
            new WalletTransactionCollection(
                $this->walletTransactionService->merchantIndex(
                    $request->user(),
                    $request->validated()
                )
            ),
            'Merchant wallet transactions retrieved successfully.'
        );
    }

    public function merchantShow(Request $request, string $uuid): JsonResponse
    {
        return $this->success(
            new WalletTransactionResource(
                $this->walletTransactionService->merchantShow($request->user(), $uuid)
            ),
            'Merchant wallet transaction retrieved successfully.'
        );
    }
}
