<?php

namespace App\Services\Wallet\Integrations;

use App\Models\Wallet;
use App\Models\WalletMerchantFunding;
use App\Services\Wallet\FundingService;

class PusoPayFundingAdapter
{
    public function __construct(
        protected FundingService $fundingService
    ) {
    }

    public function confirmFunding(
        Wallet $merchantWallet,
        string $externalReference,
        int $amountMinorUnits,
        string $currency,
        ?array $metadata = null,
    ): WalletMerchantFunding {
        return $this->fundingService->confirm(
            merchantWallet: $merchantWallet,
            provider: 'PUSOPAY',
            externalReference: $externalReference,
            amountMinorUnits: $amountMinorUnits,
            currency: $currency,
            metadata: $metadata,
        );
    }
}