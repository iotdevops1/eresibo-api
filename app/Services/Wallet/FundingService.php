<?php

namespace App\Services\Wallet;

use App\Models\Wallet;
use App\Models\WalletMerchantFunding;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FundingService
{
    public function __construct(
        protected WalletService $walletService
    ) {
    }

    public function confirm(
        Wallet $merchantWallet,
        string $provider,
        string $externalReference,
        int $amountMinorUnits,
        string $currency,
        ?array $metadata = null
    ): WalletMerchantFunding {
        if ($amountMinorUnits <= 0) {
            throw new RuntimeException('Funding amount must be greater than zero.');
        }

        $provider = strtoupper(trim($provider));
        $currency = strtoupper(trim($currency));

        if ($provider === '') {
            throw new RuntimeException('Funding provider is required.');
        }

        if ($currency === '') {
            throw new RuntimeException('Funding currency is required.');
        }

        if ($merchantWallet->status !== Wallet::STATUS_ACTIVE) {
            throw new RuntimeException('Merchant wallet is not active.');
        }

        if (strtoupper($merchantWallet->currency) !== $currency) {
            throw new RuntimeException(
                'Funding currency does not match the merchant wallet currency.'
            );
        }

        return DB::transaction(function () use (
            $merchantWallet,
            $provider,
            $externalReference,
            $amountMinorUnits,
            $currency,
            $metadata
        ) {
            /*
             * Lock the wallet so two funding requests cannot
             * update the balance at the same time.
             */
            $merchantWallet = Wallet::query()
                ->lockForUpdate()
                ->findOrFail($merchantWallet->id);

            /*
             * Idempotency:
             * provider + external_reference must only be processed once.
             */
            $funding = WalletMerchantFunding::query()
                ->where('provider', $provider)
                ->where('external_reference', $externalReference)
                ->lockForUpdate()
                ->first();

            if ($funding) {
                if ((int) $funding->wallet_id !== (int) $merchantWallet->id) {
                    throw new RuntimeException(
                        'External reference is already used for another wallet.'
                    );
                }

                if ((int) $funding->amount_minor_units !== $amountMinorUnits) {
                    throw new RuntimeException(
                        'External reference is already used with a different amount.'
                    );
                }

                if (strtoupper($funding->currency) !== $currency) {
                    throw new RuntimeException(
                        'External reference is already used with a different currency.'
                    );
                }

                if ($funding->status === WalletMerchantFunding::STATUS_CONFIRMED) {
                    return $funding;
                }
            } else {
                $funding = WalletMerchantFunding::create([
                    'wallet_id' => $merchantWallet->id,
                    'provider' => $provider,
                    'external_reference' => $externalReference,
                    'amount_minor_units' => $amountMinorUnits,
                    'amount_major_units' => $this->minorToMajor($amountMinorUnits),
                    'currency' => $currency,
                    'status' => WalletMerchantFunding::STATUS_PENDING,
                    'metadata' => $metadata,
                ]);
            }

            /*
             * Credit the merchant wallet.
             */
            $transaction = $this->walletService->credit(
                wallet: $merchantWallet,
                amountMinorUnits: $amountMinorUnits,
                reference: $provider . '-' . $externalReference,
                type: \App\Models\WalletTransaction::TYPE_FUNDING,
                description: 'Third-party wallet funding',
                metadata: [
                    'funding_id' => $funding->id,
                    'provider' => $provider,
                    'external_reference' => $externalReference,
                    ...( $metadata ?? [] ),
                ]
            );

            $funding->update([
                'wallet_id' => $merchantWallet->id,
                'status' => WalletMerchantFunding::STATUS_CONFIRMED,
                'confirmed_at' => now(),
            ]);

            return $funding->fresh([
                'wallet',
            ]);
        });
    }

    private function minorToMajor(int $minorUnits): string
    {
        return number_format($minorUnits / 100, 2, '.', '');
    }
}