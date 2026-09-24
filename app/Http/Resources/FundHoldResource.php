<?php
namespace App\Http\Resources;
use App\Models\FundHold;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class FundHoldResource extends JsonResource {public function toArray(Request $r):array{return ['uuid'=>$this->uuid,'document_reference'=>$this->document_reference,'source_type'=>$this->source_type,'description'=>$this->description,'amount_minor_units'=>$this->amount_minor_units,'amount_major_units'=>number_format($this->amount_minor_units/100,2,'.',''),'currency'=>$this->currency,'status'=>match($this->status){FundHold::STATUS_HELD=>'HELD',FundHold::STATUS_RELEASED=>'RELEASED',FundHold::STATUS_RETURNED=>'RETURNED',default=>'UNKNOWN'},'source_wallet_uuid'=>$this->sourceWallet?->uuid,'destination_wallet_uuid'=>$this->destinationWallet?->uuid,'scheduled_release_at'=>$this->scheduled_release_at?->toISOString(),'released_at'=>$this->released_at?->toISOString(),'returned_at'=>$this->returned_at?->toISOString(),'created_at'=>$this->created_at?->toISOString()];}}
