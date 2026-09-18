<?php
namespace App\Http\Resources;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletBalanceResource extends JsonResource {
    public function toArray(Request $request): array {
        $wallet=$this->resource['wallet'];
        $employee=$this->resource['employee'];
        $merchant=$this->resource['merchant'];

        return array_filter([
            'employee_no'=>$employee?->employee_no,
            'pusopay_wallet_id'=>$employee?->pusopay_wallet_id,
            'merchant_uuid'=>$merchant?->uuid,
            'merchant_code'=>$merchant?->merchant_code,
            'merchant_name'=>$merchant?->business_name,
            'wallet_uuid'=>$wallet?->uuid,
            'currency'=>'PHP',
            'available_balance_minor_units'=>$wallet?->balance_minor_units ?? 0,
            'available_balance_major_units'=>$wallet?->balance_major_units ?? '0.00',
            'status'=>match($wallet?->status) {
                Wallet::STATUS_ACTIVE=>'ACTIVE',
                Wallet::STATUS_INACTIVE=>'INACTIVE',
                Wallet::STATUS_LOCKED=>'LOCKED',
                default=>'NOT_CREATED',
            },
        ],fn($value)=>$value!==null);
    }
}
