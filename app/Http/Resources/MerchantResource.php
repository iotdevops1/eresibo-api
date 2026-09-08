<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MerchantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'merchant_code' => $this->merchant_code,
            'business_name' => $this->business_name,
            'business_type' => $this->business_type,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'address' => $this->address,
            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    1 => 'ACTIVE',
                    2 => 'INACTIVE',
                    3 => 'SUSPENDED',
                    default => 'UNKNOWN',
                },
            ],

            'employer_count' => $this->when(
                isset($this->employers_count),
                $this->employers_count
            ),

            'employee_count' => $this->when(
                isset($this->employees_count),
                $this->employees_count
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'wallet_balance_minor_units' => $this->whenLoaded(
            'wallets',
                fn () => $this->wallets->first()?->balance_minor_units ?? 0
            ),

            'wallet_balance_major_units' => $this->whenLoaded(
                'wallets',
                fn () => $this->wallets->first()?->balance_major_units ?? '0.00'
            ),

            'wallet_currency' => $this->whenLoaded(
                'wallets',
                fn () => $this->wallets->first()?->currency ?? 'PHP'
            ),
        ];
    }
}