<?php
namespace App\Http\Resources;
use App\Models\Disbursement;
use App\Models\DisbursementBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class DisbursementResource extends JsonResource {
 public function toArray(Request $request):array{return ['uuid'=>$this->uuid,'program_type'=>$this->program_type,'program_name'=>$this->program_name,'note'=>$this->note,'release_policy'=>$this->release_policy,'status'=>['id'=>$this->status,'name'=>match($this->status){Disbursement::STATUS_APPROVED=>'APPROVED',Disbursement::STATUS_RELEASED=>'RELEASED',Disbursement::STATUS_CANCELLED=>'CANCELLED',default=>'UNKNOWN'}],'currency'=>$this->currency,'beneficiary_count'=>$this->whenLoaded('beneficiaries',fn()=>$this->beneficiaries->count()),'total'=>['minor_units'=>$this->total_amount_minor_units,'major_units'=>number_format($this->total_amount_minor_units/100,2,'.',''),'currency'=>$this->currency],'beneficiaries'=>$this->whenLoaded('beneficiaries',fn()=>$this->beneficiaries->map(fn($b)=>['uuid'=>$b->uuid,'name'=>$b->beneficiary_name,'wallet_uuid'=>$b->wallet_uuid,'email'=>$b->email,'reference_no'=>$b->reference_no,'purpose'=>$b->purpose,'amount_minor_units'=>$b->amount_minor_units,'amount_major_units'=>number_format($b->amount_minor_units/100,2,'.',''),'status'=>$b->status===DisbursementBeneficiary::STATUS_RELEASED?'RELEASED':'PENDING','wallet_transaction_uuid'=>$b->walletTransaction?->uuid,'released_at'=>$b->released_at?->toISOString()])->values()),'released_at'=>$this->released_at?->toISOString(),'cancelled_at'=>$this->cancelled_at?->toISOString(),'created_at'=>$this->created_at?->toISOString()];}
}
