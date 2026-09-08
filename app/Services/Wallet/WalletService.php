<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Models\WalletEntry;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WalletService
{
    /*
    |--------------------------------------------------------------------------
    | Credit
    |--------------------------------------------------------------------------
    */

    public function credit(
        Wallet $wallet,
        int $amountMinorUnits,
        string $reference,
        string $type = WalletTransaction::TYPE_TRANSFER,
        ?string $description = null,
        ?array $metadata = null,
    ): WalletTransaction {
        $this->validateAmount($amountMinorUnits);

        return DB::transaction(function () use (
            $wallet,
            $amountMinorUnits,
            $reference,
            $type,
            $description,
            $metadata
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lock wallet row
            |--------------------------------------------------------------------------
            */

            $wallet = Wallet::query()
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate reference
            |--------------------------------------------------------------------------
            */

            if (
                WalletTransaction::query()
                    ->where('reference', $reference)
                    ->exists()
            ) {
                throw ValidationException::withMessages([
                    'reference' => [
                        'Wallet transaction reference already exists.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate balances
            |--------------------------------------------------------------------------
            */

            $balanceBeforeMinorUnits = (int) $wallet->balance_minor_units;
            $balanceAfterMinorUnits  = $balanceBeforeMinorUnits + $amountMinorUnits;
            $balanceBeforeMajorUnits = $this->minorToMajor($balanceBeforeMinorUnits);
            $balanceAfterMajorUnits  = $this->minorToMajor($balanceAfterMinorUnits);
            $amountMajorUnits        = $this->minorToMajor($amountMinorUnits);

            /*
            |--------------------------------------------------------------------------
            | Create transaction
            |--------------------------------------------------------------------------
            */

            $transaction = WalletTransaction::create([
                'reference'          => $reference,
                'type'               => $type,
                'status'             => WalletTransaction::STATUS_COMPLETED,
                'amount_minor_units' => $amountMinorUnits,
                'amount_major_units' => $amountMajorUnits,
                'currency'           => $wallet->currency,
                'from_wallet_id'     => null,
                'to_wallet_id'       => $wallet->id,
                'description'        => $description,
                'metadata'           => $metadata,
                'completed_at'       => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update wallet balance
            |--------------------------------------------------------------------------
            */

            $wallet->update([
                'balance_minor_units' => $balanceAfterMinorUnits,
                'balance_major_units' => $balanceAfterMajorUnits,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create CREDIT entry
            |--------------------------------------------------------------------------
            */

            WalletEntry::create([
                'wallet_transaction_id'      => $transaction->id,
                'wallet_id'                  => $wallet->id,
                'entry_type'                 => WalletEntry::TYPE_CREDIT,
                'amount_minor_units'         => $amountMinorUnits,
                'amount_major_units'         => $amountMajorUnits,
                'balance_before_minor_units' => $balanceBeforeMinorUnits,
                'balance_before_major_units' => $balanceBeforeMajorUnits,
                'balance_after_minor_units'  => $balanceAfterMinorUnits,
                'balance_after_major_units'  => $balanceAfterMajorUnits,
            ]);

            return $transaction->load('entries');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Debit
    |--------------------------------------------------------------------------
    */

    public function debit(
        Wallet $wallet,
        int $amountMinorUnits,
        string $reference,
        string $type = WalletTransaction::TYPE_TRANSFER,
        ?string $description = null,
        ?array $metadata = null,
    ): WalletTransaction {
        $this->validateAmount($amountMinorUnits);

        return DB::transaction(function () use (
            $wallet,
            $amountMinorUnits,
            $reference,
            $type,
            $description,
            $metadata
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lock wallet row
            |--------------------------------------------------------------------------
            */

            $wallet = Wallet::query()
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate reference
            |--------------------------------------------------------------------------
            */

            if (WalletTransaction::query()->where('reference', $reference)->exists()) {
                throw ValidationException::withMessages([
                    'reference' => [
                        'Wallet transaction reference already exists.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Check sufficient balance
            |--------------------------------------------------------------------------
            */

            $balanceBeforeMinorUnits =
                (int) $wallet->balance_minor_units;

            if ($balanceBeforeMinorUnits < $amountMinorUnits) {
                throw ValidationException::withMessages([
                    'amount_minor_units' => [
                        'Insufficient wallet balance.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate balances
            |--------------------------------------------------------------------------
            */

            $balanceAfterMinorUnits  = $balanceBeforeMinorUnits - $amountMinorUnits;
            $balanceBeforeMajorUnits = $this->minorToMajor($balanceBeforeMinorUnits);
            $balanceAfterMajorUnits  = $this->minorToMajor($balanceAfterMinorUnits);
            $amountMajorUnits        = $this->minorToMajor($amountMinorUnits);

            /*
            |--------------------------------------------------------------------------
            | Create transaction
            |--------------------------------------------------------------------------
            */

            $transaction = WalletTransaction::create([
                'reference'          => $reference,
                'type'               => $type,
                'status'             => WalletTransaction::STATUS_COMPLETED,
                'amount_minor_units' => $amountMinorUnits,
                'amount_major_units' => $amountMajorUnits,
                'currency'           => $wallet->currency,
                'from_wallet_id'     => $wallet->id,
                'to_wallet_id'       => null,
                'description'        => $description,
                'metadata'           => $metadata,
                'completed_at'       => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update wallet balance
            |--------------------------------------------------------------------------
            */

            $wallet->update([
                'balance_minor_units' => $balanceAfterMinorUnits,
                'balance_major_units' => $balanceAfterMajorUnits,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create DEBIT entry
            |--------------------------------------------------------------------------
            */

            WalletEntry::create([
                'wallet_transaction_id' => $transaction->id,
                'wallet_id' => $wallet->id,

                'entry_type' => WalletEntry::TYPE_DEBIT,

                'amount_minor_units' => $amountMinorUnits,
                'amount_major_units' => $amountMajorUnits,

                'balance_before_minor_units' =>
                    $balanceBeforeMinorUnits,

                'balance_before_major_units' =>
                    $balanceBeforeMajorUnits,

                'balance_after_minor_units' =>
                    $balanceAfterMinorUnits,

                'balance_after_major_units' =>
                    $balanceAfterMajorUnits,
            ]);

            return $transaction->load('entries');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Transfer
    |--------------------------------------------------------------------------
    */

    public function transfer(
        Wallet $fromWallet,
        Wallet $toWallet,
        int $amountMinorUnits,
        string $reference,
        string $type = WalletTransaction::TYPE_TRANSFER,
        ?string $description = null,
        ?array $metadata = null,
    ): WalletTransaction {
        $this->validateAmount($amountMinorUnits);

        if ($fromWallet->id === $toWallet->id) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'Source and destination wallets must be different.',
                ],
            ]);
        }

        return DB::transaction(function () use (
            $fromWallet,
            $toWallet,
            $amountMinorUnits,
            $reference,
            $type,
            $description,
            $metadata
        ) {
            /*
            |--------------------------------------------------------------------------
            | Lock wallets in deterministic order
            |--------------------------------------------------------------------------
            |
            | This reduces the chance of deadlocks when two transfers happen
            | between the same wallets at the same time.
            |
            */

            $walletIds = [
                $fromWallet->id,
                $toWallet->id,
            ];

            sort($walletIds);

            $lockedWallets = Wallet::query()
                ->whereIn('id', $walletIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $sourceWallet = $lockedWallets->get(
                $fromWallet->id
            );

            $destinationWallet = $lockedWallets->get(
                $toWallet->id
            );

            if (! $sourceWallet || ! $destinationWallet) {
                throw ValidationException::withMessages([
                    'wallet' => [
                        'One or more wallets could not be found.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Verify same currency
            |--------------------------------------------------------------------------
            */

            if ($sourceWallet->currency !== $destinationWallet->currency) {
                throw ValidationException::withMessages([
                    'currency' => [
                        'Wallet currencies must match.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate reference
            |--------------------------------------------------------------------------
            */

            if (WalletTransaction::query()->where('reference', $reference)->exists()) {
                throw ValidationException::withMessages([
                    'reference' => [
                        'Wallet transaction reference already exists.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Check balance
            |--------------------------------------------------------------------------
            */

            $sourceBeforeMinorUnits = (int) $sourceWallet->balance_minor_units;

            if ($sourceBeforeMinorUnits < $amountMinorUnits) {
                throw ValidationException::withMessages([
                    'amount_minor_units' => [
                        'Insufficient wallet balance.',
                    ],
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Calculate balances
            |--------------------------------------------------------------------------
            */

            $sourceAfterMinorUnits       = $sourceBeforeMinorUnits - $amountMinorUnits;
            $destinationBeforeMinorUnits = (int) $destinationWallet->balance_minor_units;
            $destinationAfterMinorUnits  = $destinationBeforeMinorUnits + $amountMinorUnits;
            $amountMajorUnits            = $this->minorToMajor($amountMinorUnits);
            $sourceBeforeMajorUnits      = $this->minorToMajor($sourceBeforeMinorUnits);
            $sourceAfterMajorUnits       = $this->minorToMajor($sourceAfterMinorUnits);
            $destinationBeforeMajorUnits = $this->minorToMajor($destinationBeforeMinorUnits);
            $destinationAfterMajorUnits  = $this->minorToMajor($destinationAfterMinorUnits);

            /*
            |--------------------------------------------------------------------------
            | Create transaction
            |--------------------------------------------------------------------------
            */

            $transaction = WalletTransaction::create([
                'reference' => $reference,
                'type' => $type,
                'status' => WalletTransaction::STATUS_COMPLETED,

                'amount_minor_units' => $amountMinorUnits,
                'amount_major_units' => $amountMajorUnits,

                'currency' => $sourceWallet->currency,

                'from_wallet_id' => $sourceWallet->id,
                'to_wallet_id' => $destinationWallet->id,

                'description' => $description,
                'metadata' => $metadata,

                'completed_at' => now(),
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update source wallet
            |--------------------------------------------------------------------------
            */

            $sourceWallet->update([
                'balance_minor_units' => $sourceAfterMinorUnits,
                'balance_major_units' => $sourceAfterMajorUnits,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Update destination wallet
            |--------------------------------------------------------------------------
            */

            $destinationWallet->update([
                'balance_minor_units' => $destinationAfterMinorUnits,
                'balance_major_units' => $destinationAfterMajorUnits,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create DEBIT entry
            |--------------------------------------------------------------------------
            */

            WalletEntry::create([
                'wallet_transaction_id'      => $transaction->id,
                'wallet_id'                  => $sourceWallet->id,
                'entry_type'                 => WalletEntry::TYPE_DEBIT,
                'amount_minor_units'         => $amountMinorUnits,
                'amount_major_units'         => $amountMajorUnits,
                'balance_before_minor_units' => $sourceBeforeMinorUnits,
                'balance_before_major_units' => $sourceBeforeMajorUnits,
                'balance_after_minor_units'  => $sourceAfterMinorUnits,
                'balance_after_major_units'  => $sourceAfterMajorUnits,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Create CREDIT entry
            |--------------------------------------------------------------------------
            */

            WalletEntry::create([
                'wallet_transaction_id'      => $transaction->id,
                'wallet_id'                  => $destinationWallet->id,
                'entry_type'                 => WalletEntry::TYPE_CREDIT,
                'amount_minor_units'         => $amountMinorUnits,
                'amount_major_units'         => $amountMajorUnits,
                'balance_before_minor_units' =>  $destinationBeforeMinorUnits,
                'balance_before_major_units' => $destinationBeforeMajorUnits,
                'balance_after_minor_units'  => $destinationAfterMinorUnits,
                'balance_after_major_units'  => $destinationAfterMajorUnits,
            ]);

            return $transaction->load('entries');
        });
    }

    public function prefundMerchant(
        Wallet $mainWallet,
        Wallet $merchantWallet,
        int $amountMinorUnits,
        string $reference,
        ?string $description = null,
        ?array $metadata = null,
    ): WalletTransaction {
        $this->validateAmount($amountMinorUnits);

        if ($mainWallet->owner_type !== Wallet::OWNER_TYPE_MAIN) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'Source wallet must be the eResibo Main Wallet.',
                ],
            ]);
        }

        if ($merchantWallet->owner_type !== Wallet::OWNER_TYPE_MERCHANT) {
            throw ValidationException::withMessages([
                'wallet' => [
                    'Destination wallet must be a Merchant wallet.',
                ],
            ]);
        }

        return $this->transfer(
            $mainWallet,
            $merchantWallet,
            $amountMinorUnits,
            $reference,
            WalletTransaction::TYPE_PREFUND,
            $description,
            $metadata
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Validate amount
    |--------------------------------------------------------------------------
    */

    private function validateAmount(int $amountMinorUnits): void
    {
        if ($amountMinorUnits <= 0) {
            throw ValidationException::withMessages([
                'amount_minor_units' => [
                    'Amount must be greater than zero.',
                ],
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Convert minor → major
    |--------------------------------------------------------------------------
    |
    | PHP integer cents/pesos → display amount.
    |
    */

    private function minorToMajor(int $amountMinorUnits): string
    {
        return number_format($amountMinorUnits / 100, 2, '.', '');
    }
}