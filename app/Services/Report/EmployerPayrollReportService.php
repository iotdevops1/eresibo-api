<?php
namespace App\Services\Report;
use App\Models\Dispute;
use App\Models\PayrollBatch;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Validation\ValidationException;
class EmployerPayrollReportService {
 public function report(User $user,array $filters):array {
  if(!$user->merchant_id)throw ValidationException::withMessages(['merchant'=>['Employer is not assigned to a merchant.']]);
  $start=isset($filters['start_date'])?now()->parse($filters['start_date'])->startOfDay():now()->startOfYear();
  $end=isset($filters['end_date'])?now()->parse($filters['end_date'])->endOfDay():now()->endOfDay();
  $batches=PayrollBatch::query()->where('merchant_id',$user->merchant_id)->whereBetween('pay_date',[$start->toDateString(),$end->toDateString()])->orderByDesc('pay_date')->get();
  $payslips=Payslip::query()->where('merchant_id',$user->merchant_id)->whereBetween('pay_date',[$start->toDateString(),$end->toDateString()])->get();
  $ack=$payslips->where('status',Payslip::STATUS_ACKNOWLEDGED);
  $disputes=Dispute::query()->where('merchant_id',$user->merchant_id)->whereBetween('created_at',[$start,$end])->get();
  $amount=fn($minor)=>['minor_units'=>(int)$minor,'major_units'=>number_format($minor/100,2,'.',''),'currency'=>'PHP'];
  return ['period'=>['start'=>$start->toDateString(),'end'=>$end->toDateString()],'summary'=>[
   'net_disbursed'=>$amount($ack->sum('net_amount_minor_units')),'total_deductions'=>$amount($payslips->sum('deduction_amount_minor_units')),
   'payslips_acknowledged'=>['acknowledged'=>$ack->count(),'total'=>$payslips->count(),'rate_percent'=>$payslips->isEmpty()?0:round($ack->count()/$payslips->count()*100,1)],
   'disputes'=>['total'=>$disputes->count(),'open'=>$disputes->whereIn('status',[Dispute::STATUS_OPEN,Dispute::STATUS_PENDING])->count(),'resolved'=>$disputes->where('status',Dispute::STATUS_RESOLVED)->count()]
  ],'rows'=>$batches->map(fn($batch)=>['batch_no'=>$batch->batch_no,'period_start'=>$batch->pay_period_start?->toDateString(),'period_end'=>$batch->pay_period_end?->toDateString(),'pay_date'=>$batch->pay_date?->toDateString(),'staff'=>$batch->total_employees,'gross_minor_units'=>(int)round($batch->total_gross_amount*100),'deductions_minor_units'=>(int)round($batch->total_deduction_amount*100),'net_minor_units'=>(int)round($batch->total_net_amount*100),'gross'=>$batch->total_gross_amount,'deductions'=>$batch->total_deduction_amount,'net'=>$batch->total_net_amount])->values()->all()];
 }
}
