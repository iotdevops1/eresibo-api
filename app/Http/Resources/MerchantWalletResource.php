<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Wallet;

class MerchantWalletResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $merchant = $this->merchant;

        return [
            'merchant_uuid' => $merchant?->uuid,
            'merchant_code' => $merchant?->merchant_code,
            'merchant_name' => $merchant?->business_name,

            'wallet_uuid' => $this->uuid,
            'wallet_name' => $this->name,
            'currency' => $this->currency,

            'balance_minor_units' => $this->balance_minor_units,
            'balance_major_units' => $this->balance_major_units,

            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    Wallet::STATUS_ACTIVE => 'ACTIVE',
                    Wallet::STATUS_INACTIVE => 'INACTIVE',
                    Wallet::STATUS_LOCKED => 'LOCKED',
                    default => 'UNKNOWN',
                },
            ],
        ];
    }
}