<?php

namespace App\Services\Wallet;

use App\Models\User;
use App\Models\WalletTransaction;
use App\Repositories\Wallet\WalletTransactionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class WalletTransactionService
{
    public function __construct(
        protected WalletBalanceService $walletBalanceService,
        protected WalletTransactionRepository $walletTransactionRepository,
    ) {
    }

    public function employeeIndex(User $user, array $filters): LengthAwarePaginator
    {
        return $this->paginate($this->employeeWalletIds($user), $filters);
    }

    public function employerIndex(User $user, array $filters): LengthAwarePaginator
    {
        return $this->paginate($this->employerWalletIds($user), $filters);
    }

    public function merchantIndex(User $user, array $filters): LengthAwarePaginator
    {
        return $this->paginate($this->merchantWalletIds($user), $filters);
    }

    public function employeeShow(User $user, string $uuid): WalletTransaction
    {
        return $this->show($uuid, $this->employeeWalletIds($user));
    }

    public function employerShow(User $user, string $uuid): WalletTransaction
    {
        return $this->show($uuid, $this->employerWalletIds($user));
    }

    public function merchantShow(User $user, string $uuid): WalletTransaction
    {
        return $this->show($uuid, $this->merchantWalletIds($user));
    }

    private function paginate(array $walletIds, array $filters): LengthAwarePaginator
    {
        $transactions = $this->walletTransactionRepository
            ->paginateForWalletIds($walletIds, $filters);

        $transactions->getCollection()->each(
            fn (WalletTransaction $transaction) => $this->setDirection($transaction, $walletIds)
        );

        return $transactions;
    }

    private function show(string $uuid, array $walletIds): WalletTransaction
    {
        $transaction = $this->walletTransactionRepository
            ->findByUuidForWalletIds($uuid, $walletIds);

        if (! $transaction) {
            throw ValidationException::withMessages([
                'transaction' => ['Transaction not found.'],
            ]);
        }

        return $this->setDirection($transaction, $walletIds);
    }

    private function employeeWalletIds(User $user): array
    {
        return $this->walletIdsFromBalance(
            $this->walletBalanceService->employee($user)
        );
    }

    private function employerWalletIds(User $user): array
    {
        return $this->walletIdsFromBalance(
            $this->walletBalanceService->employer($user)
        );
    }

    private function merchantWalletIds(User $user): array
    {
        return $this->walletIdsFromBalance(
            $this->walletBalanceService->merchant($user)
        );
    }

    private function walletIdsFromBalance(array $balance): array
    {
        return $balance['wallet'] ? [$balance['wallet']->id] : [0];
    }

    private function setDirection(WalletTransaction $transaction, array $walletIds): WalletTransaction
    {
        $fromCurrentWallet = in_array($transaction->from_wallet_id, $walletIds, true);
        $toCurrentWallet = in_array($transaction->to_wallet_id, $walletIds, true);

        $transaction->setAttribute('transaction_direction', match (true) {
            $fromCurrentWallet && $toCurrentWallet => 'INTERNAL',
            $fromCurrentWallet => 'OUTGOING',
            $toCurrentWallet => 'INCOMING',
            default => 'UNKNOWN',
        });

        return $transaction;
    }
}
