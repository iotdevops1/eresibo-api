<?php
namespace App\Services\Payslip;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipLine;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class PayslipService {
    public function __construct(protected WalletService $walletService,protected PayslipReceiptService $receiptService){}
    public function create(int $merchantId,int $issuerId,array $data):Payslip {
        return DB::transaction(function()use($merchantId,$issuerId,$data){
            $employee=Employee::query()->where('employee_no',$data['employee_no'])->where('merchant_id',$merchantId)->lockForUpdate()->first();
            if(!$employee)throw ValidationException::withMessages(['employee_no'=>['Employee number is not registered under your employer account.']]);
            if($employee->status!==Employee::STATUS_ACTIVE)throw ValidationException::withMessages(['employee_no'=>['Employee is not active and cannot receive a payslip.']]);
            if(!$employee->user_id)throw ValidationException::withMessages(['employee_no'=>['Employee does not have a linked account.']]);
            $gross=(int)collect($data['earnings'])->sum('amount_minor_units');$deductions=(int)collect($data['deductions']??[])->sum('amount_minor_units');$net=$gross-$deductions;
            if($net<=0)throw ValidationException::withMessages(['deductions'=>['Deductions must be less than total earnings.']]);
            $payslip=Payslip::create(['uuid'=>(string)Str::uuid(),'merchant_id'=>$merchantId,'employee_id'=>$employee->id,'issued_by_user_id'=>$issuerId,'pay_period_start'=>$data['pay_period_start'],'pay_period_end'=>$data['pay_period_end'],'pay_date'=>$data['pay_date'],'note'=>$data['note']??null,'currency'=>'PHP','gross_amount_minor_units'=>$gross,'deduction_amount_minor_units'=>$deductions,'net_amount_minor_units'=>$net,'gross_amount_major_units'=>$this->major($gross),'deduction_amount_major_units'=>$this->major($deductions),'net_amount_major_units'=>$this->major($net),'status'=>Payslip::STATUS_PENDING_ACKNOWLEDGEMENT]);
            $this->lines($payslip,$data['earnings'],PayslipLine::TYPE_EARNING);$this->lines($payslip,$data['deductions']??[],PayslipLine::TYPE_DEDUCTION);
            return $payslip->load(['employee','lines']);
        });
    }
    public function acknowledge(string $uuid,int $employeeUserId):Payslip {
        return DB::transaction(function()use($uuid,$employeeUserId){
            $payslip=Payslip::query()->where('uuid',$uuid)->where('status',Payslip::STATUS_PENDING_ACKNOWLEDGEMENT)->whereHas('employee',fn($q)=>$q->where('user_id',$employeeUserId))->lockForUpdate()->first();
            if(!$payslip)throw ValidationException::withMessages(['payslip'=>['Pending payslip not found for the authenticated employee.']]);
            $source=Wallet::query()->where('owner_type',Wallet::OWNER_TYPE_MERCHANT)->where('owner_id',$payslip->merchant_id)->where('currency',$payslip->currency)->where('status',Wallet::STATUS_ACTIVE)->lockForUpdate()->first();
            if(!$source)throw ValidationException::withMessages(['wallet'=>['Active merchant wallet not found.']]);
            $destination=Wallet::query()->firstOrCreate(['owner_type'=>Wallet::OWNER_TYPE_USER,'owner_id'=>$employeeUserId,'currency'=>$payslip->currency],['name'=>'Employee wallet - '.$payslip->employee->employee_no,'balance_minor_units'=>0,'balance_major_units'=>0,'status'=>Wallet::STATUS_ACTIVE]);
            if($destination->status!==Wallet::STATUS_ACTIVE)throw ValidationException::withMessages(['wallet'=>['Employee wallet is not active.']]);
            $transaction=$this->walletService->transfer($source,$destination,$payslip->net_amount_minor_units,'PAYSLIP-'.$payslip->uuid,WalletTransaction::TYPE_PAYROLL,'Acknowledged payslip',['payslip_uuid'=>$payslip->uuid,'employee_id'=>$payslip->employee_id]);
            $receipt=$this->receiptService->create($payslip,$transaction);
            $payslip->update(['source_wallet_id'=>$source->id,'destination_wallet_id'=>$destination->id,'wallet_transaction_id'=>$transaction->id,'receipt_id'=>$receipt->id,'status'=>Payslip::STATUS_ACKNOWLEDGED,'acknowledged_at'=>now()]);
            return $payslip->refresh()->load(['employee','lines','walletTransaction','receipt']);
        });
    }
    private function lines(Payslip $p,array $lines,string $type):void{foreach($lines as $i=>$line){$amount=(int)$line['amount_minor_units'];$p->lines()->create(['line_type'=>$type,'description'=>$line['description'],'amount_minor_units'=>$amount,'amount_major_units'=>$this->major($amount),'sort_order'=>$i+1]);}}
    private function major(int $minor):string{return number_format($minor/100,2,'.','');}
}
