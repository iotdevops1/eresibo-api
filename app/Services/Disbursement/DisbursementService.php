<?php
namespace App\Services\Disbursement;
use App\Models\Disbursement;
use App\Models\DisbursementBeneficiary;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
class DisbursementService {
 public function __construct(private WalletService $wallets) {}
 public function index(User $user,array $filters):LengthAwarePaginator {
  $query=Disbursement::query()->where('merchant_id',$this->merchantId($user))->with('beneficiaries');
  if($search=$filters['search']??null)$query->where('program_name','like',"%{$search}%");
  if($type=$filters['program_type']??null)$query->where('program_type',$type);
  if($status=$filters['status']??null)$query->where('status',match($status){'APPROVED'=>Disbursement::STATUS_APPROVED,'RELEASED'=>Disbursement::STATUS_RELEASED,'CANCELLED'=>Disbursement::STATUS_CANCELLED});
  return $query->latest()->paginate($filters['per_page']??15);
 }
 public function show(User $user,string $uuid):Disbursement { return $this->find($user,$uuid); }
 public function create(User $user,array $data):Disbursement {
  return DB::transaction(function()use($user,$data){$total=collect($data['beneficiaries'])->sum('amount_minor_units');$d=Disbursement::create(['merchant_id'=>$this->merchantId($user),'created_by_user_id'=>$user->id,'program_type'=>$data['program_type'],'program_name'=>$data['program_name'],'note'=>$data['note']??null,'release_policy'=>$data['release_policy'],'status'=>Disbursement::STATUS_APPROVED,'currency'=>'PHP','total_amount_minor_units'=>$total,'total_amount_major_units'=>number_format($total / 100, 2, '.', '')]);
   foreach($data['beneficiaries'] as $b)$d->beneficiaries()->create(['beneficiary_name'=>$b['beneficiary_name'],'wallet_uuid'=>$b['wallet_uuid']??null,'email'=>$b['email']??null,'reference_no'=>$b['reference_no']??null,'purpose'=>$b['purpose']??null,'amount_minor_units'=>$b['amount_minor_units'],'amount_major_units'=>number_format($b['amount_minor_units'] / 100, 2, '.', ''),'status'=>DisbursementBeneficiary::STATUS_PENDING]);
   return $d->load(['beneficiaries.walletTransaction']);
  });
 }
 public function release(User $user,string $uuid):Disbursement {
  return DB::transaction(function()use($user,$uuid){$d=$this->find($user,$uuid);if($d->status!==Disbursement::STATUS_APPROVED)throw ValidationException::withMessages(['disbursement'=>['Only an approved disbursement can be released.']]);
   $source=Wallet::query()->where('owner_type',Wallet::OWNER_TYPE_MERCHANT)->where('owner_id',$d->merchant_id)->where('status',Wallet::STATUS_ACTIVE)->first();
   if(!$source)throw ValidationException::withMessages(['wallet'=>['Active merchant wallet was not found.']]);
   foreach($d->beneficiaries as $b){if(!$b->wallet_uuid)throw ValidationException::withMessages(['beneficiaries'=>["Beneficiary {$b->beneficiary_name} needs a destination wallet before release."]]);$destination=Wallet::query()->where('uuid',$b->wallet_uuid)->where('status',Wallet::STATUS_ACTIVE)->first();if(!$destination)throw ValidationException::withMessages(['beneficiaries'=>["Active wallet was not found for {$b->beneficiary_name}."]]);$tx=$this->wallets->transfer($source,$destination,$b->amount_minor_units,'DISB-'.$d->uuid.'-'.$b->uuid,WalletTransaction::TYPE_TRANSFER,'Disbursement: '.$d->program_name,['disbursement_uuid'=>$d->uuid,'beneficiary_uuid'=>$b->uuid,'purpose'=>$b->purpose]);$b->update(['status'=>DisbursementBeneficiary::STATUS_RELEASED,'wallet_transaction_id'=>$tx->id,'released_at'=>now()]);}
   $d->update(['status'=>Disbursement::STATUS_RELEASED,'released_at'=>now()]);return $d->fresh()->load(['beneficiaries.walletTransaction']);
  });
 }
 public function cancel(User $user,string $uuid):Disbursement {$d=$this->find($user,$uuid);if($d->status!==Disbursement::STATUS_APPROVED)throw ValidationException::withMessages(['disbursement'=>['Only an approved disbursement can be cancelled.']]);$d->update(['status'=>Disbursement::STATUS_CANCELLED,'cancelled_at'=>now()]);return $d->fresh()->load(['beneficiaries.walletTransaction']);}
 private function find(User $user,string $uuid):Disbursement{return Disbursement::query()->where('merchant_id',$this->merchantId($user))->where('uuid',$uuid)->with(['beneficiaries.walletTransaction'])->firstOrFail();}
 private function merchantId(User $user):int {if(!$user->merchant_id)throw ValidationException::withMessages(['merchant'=>['Employer is not assigned to a merchant.']]);return $user->merchant_id;}
}
