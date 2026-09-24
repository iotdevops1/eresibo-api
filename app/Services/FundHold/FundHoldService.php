<?php
namespace App\Services\FundHold;
use App\Models\FundHold;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\Wallet\WalletService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class FundHoldService {
 public function __construct(private WalletService $wallets){}
 public function index(User $u,array $f){$q=FundHold::query()->where('merchant_id',$this->merchant($u))->with(['sourceWallet','destinationWallet']);if(($f['status']??'HELD')!=='ALL')$q->where('status',match($f['status']??'HELD'){'HELD'=>1,'RELEASED'=>2,'RETURNED'=>3});if($s=$f['search']??null)$q->where(fn($q)=>$q->where('document_reference','like',"%$s%")->orWhere('source_type','like',"%$s%"));return $q->latest()->paginate($f['per_page']??15);}
 public function summary(User $u):array{$q=FundHold::query()->where('merchant_id',$this->merchant($u));$held=(clone $q)->where('status',1);return ['currently_held'=>$held->count(),'held_amount_minor_units'=>(int)$held->sum('amount_minor_units'),'scheduled'=>(clone $held)->whereNotNull('scheduled_release_at')->count(),'with_concerns'=>0,'currency'=>'PHP'];}
 public function show(User $u,string $id):FundHold{return $this->find($u,$id);}
 public function create(User $u,array $d):FundHold{return DB::transaction(function()use($u,$d){$merchant=$this->merchant($u);$source=Wallet::query()->where('owner_type',Wallet::OWNER_TYPE_MERCHANT)->where('owner_id',$merchant)->where('status',Wallet::STATUS_ACTIVE)->first();$dest=Wallet::query()->where('uuid',$d['destination_wallet_uuid'])->where('status',Wallet::STATUS_ACTIVE)->first();if(!$source||!$dest)throw ValidationException::withMessages(['wallet'=>['An active source and destination wallet are required.']]);$reference='HOLD-'.Str::upper(Str::random(12));$this->wallets->transfer($source,$dest,$d['amount_minor_units'],$reference,WalletTransaction::TYPE_TRANSFER,'Fund hold: '.$d['source_type']);$dest->refresh()->update(['held_minor_units'=>(int)$dest->held_minor_units+$d['amount_minor_units'],'held_major_units'=>number_format(((int)$dest->held_minor_units+$d['amount_minor_units'])/100,2,'.','')]);return FundHold::create(['merchant_id'=>$merchant,'created_by_user_id'=>$u->id,'source_wallet_id'=>$source->id,'destination_wallet_id'=>$dest->id,'document_reference'=>$reference,'source_type'=>$d['source_type'],'description'=>$d['description']??null,'amount_minor_units'=>$d['amount_minor_units'],'amount_major_units'=>number_format($d['amount_minor_units']/100,2,'.',''),'currency'=>$source->currency,'status'=>FundHold::STATUS_HELD,'scheduled_release_at'=>$d['scheduled_release_at']??null]);});}
 public function release(User $u,string $id):FundHold{return $this->change($u,$id,'release');}
 public function override(User $u,string $id):FundHold{return $this->change($u,$id,'release');}
 public function return(User $u,string $id):FundHold{return DB::transaction(function()use($u,$id){$h=$this->find($u,$id);$this->assertHeld($h);$dest=Wallet::query()->lockForUpdate()->findOrFail($h->destination_wallet_id);$this->unhold($dest,$h->amount_minor_units);$this->wallets->transfer($dest,Wallet::findOrFail($h->source_wallet_id),$h->amount_minor_units,$h->document_reference.'-RETURN',WalletTransaction::TYPE_TRANSFER,'Returned fund hold');$h->update(['status'=>FundHold::STATUS_RETURNED,'returned_at'=>now()]);return $h->fresh(['sourceWallet','destinationWallet']);});}
 private function change(User $u,string $id,string $action):FundHold{return DB::transaction(function()use($u,$id){$h=$this->find($u,$id);$this->assertHeld($h);$dest=Wallet::query()->lockForUpdate()->findOrFail($h->destination_wallet_id);$this->unhold($dest,$h->amount_minor_units);$h->update(['status'=>FundHold::STATUS_RELEASED,'released_at'=>now()]);return $h->fresh(['sourceWallet','destinationWallet']);});}
 private function unhold(Wallet $wallet,int $amount):void{if((int)$wallet->held_minor_units<$amount)throw ValidationException::withMessages(['fund_hold'=>['Held balance is inconsistent.']]);$held=(int)$wallet->held_minor_units-$amount;$wallet->update(['held_minor_units'=>$held,'held_major_units'=>number_format($held/100,2,'.','')]);}
 private function find(User $u,string $id):FundHold{return FundHold::query()->where('merchant_id',$this->merchant($u))->where('uuid',$id)->with(['sourceWallet','destinationWallet'])->firstOrFail();}
 private function assertHeld(FundHold $h):void{if($h->status!==FundHold::STATUS_HELD)throw ValidationException::withMessages(['fund_hold'=>['This fund hold is no longer active.']]);}
 private function merchant(User $u):int{if(!$u->merchant_id)throw ValidationException::withMessages(['merchant'=>['Employer is not assigned to a merchant.']]);return $u->merchant_id;}
}
