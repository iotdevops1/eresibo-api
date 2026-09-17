<?php
namespace App\Services\Payslip;
use App\Models\Merchant;
use App\Models\Payslip;
use App\Models\Receipt;
use App\Models\WalletTransaction;
class PayslipReceiptService {
    public function create(Payslip $payslip,WalletTransaction $transaction):Receipt {
        if ($payslip->receipt_id) return Receipt::query()->findOrFail($payslip->receipt_id);
        $merchant=Merchant::query()->findOrFail($payslip->merchant_id);
        return Receipt::create([
            'source_system'=>'ERESIBO',
            'external_reference'=>$transaction->reference,
            'amount_minor'=>$payslip->net_amount_minor_units,
            'currency'=>$payslip->currency,
            'transaction_type'=>'SALARY_DISBURSEMENT',
            'counterparty_label'=>$merchant->business_name,
            'occurred_at'=>now(),
            'public_token'=>$this->publicToken(),
            'expires_at'=>now()->addDays(config('eresibo.receipt_expiry_days',90)),
            'status'=>Receipt::STATUS_CONFIRMED,
            'processed_at'=>now(),
        ]);
    }
    private function publicToken():string {
        do {$token=rtrim(strtr(base64_encode(random_bytes(16)),'+/','-_'),'=');}
        while(Receipt::query()->where('public_token',$token)->exists());
        return $token;
    }
}
