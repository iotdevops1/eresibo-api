<?php
namespace App\Http\Resources;
use App\Models\DocumentVaultDocument;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class DocumentVaultDocumentResource extends JsonResource {
    public function toArray(Request $request):array{return [
        'uuid'=>$this->uuid,'type'=>$this->document_type,'title'=>$this->title,'reference'=>$this->reference,
        'document_date'=>$this->document_date?->toISOString(),'status'=>$this->archived_at?'ARCHIVED':'ACTIVE','archived_at'=>$this->archived_at?->toISOString(),
        'merchant'=>$this->whenLoaded('merchant',fn()=> $this->merchant?['uuid'=>$this->merchant->uuid,'merchant_code'=>$this->merchant->merchant_code,'business_name'=>$this->merchant->business_name]:null),
        'source'=>['uuid'=>$this->source_uuid,'type'=>$this->document_type],
        'links'=>['self'=>url('/api/employee/documents/'.$this->uuid),'source'=>$this->document_type===DocumentVaultDocument::TYPE_PAYSLIP?url('/api/employee/payslips/'.$this->source_uuid):null],
        'created_at'=>$this->created_at?->toISOString(),'updated_at'=>$this->updated_at?->toISOString(),
    ];}
}
