<?php
namespace App\Http\Resources;
use App\Models\Payslip;
use App\Models\PayslipLine;
use Illuminate\Http\Resources\Json\JsonResource;
class PayslipResource extends JsonResource {
    public function toArray($request):array{return [
        'uuid'=>$this->uuid,'employee'=>['uuid'=>$this->employee->uuid,'employee_no'=>$this->employee->employee_no,'name'=>$this->employee->full_name],
        'pay_period'=>['start'=>$this->pay_period_start?->format('Y-m-d'),'end'=>$this->pay_period_end?->format('Y-m-d')],'pay_date'=>$this->pay_date?->format('Y-m-d'),'note'=>$this->note,'currency'=>$this->currency,
        'earnings'=>$this->lines->where('line_type',PayslipLine::TYPE_EARNING)->values(),'deductions'=>$this->lines->where('line_type',PayslipLine::TYPE_DEDUCTION)->values(),
        'totals'=>['gross_minor_units'=>$this->gross_amount_minor_units,'deductions_minor_units'=>$this->deduction_amount_minor_units,'net_minor_units'=>$this->net_amount_minor_units,'gross'=>$this->gross_amount_major_units,'deductions'=>$this->deduction_amount_major_units,'net'=>$this->net_amount_major_units],
        'status'=>$this->status===Payslip::STATUS_ACKNOWLEDGED?'ACKNOWLEDGED':'PENDING_ACKNOWLEDGEMENT','payout'=>['wallet_transaction_uuid'=>$this->walletTransaction?->uuid],
        'receipt'=>$this->receipt?['uuid'=>$this->receipt->uuid,'reference'=>$this->receipt->external_reference,'amount_minor'=>$this->receipt->amount_minor,'amount_major'=>number_format($this->receipt->amount_minor/100,2,'.',''),'currency'=>$this->receipt->currency,'transaction_type'=>'Salary disbursement','counterparty'=>$this->receipt->counterparty_label,'occurred_at'=>$this->receipt->occurred_at?->toISOString(),'note'=>$this->note,'verification_url'=>$this->receipt->public_url]:null,
        'acknowledged_at'=>$this->acknowledged_at?->toISOString(),
    ];}
}
