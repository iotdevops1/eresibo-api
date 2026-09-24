<?php
namespace App\Http\Requests\DocumentVault;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class DocumentVaultFilterRequest extends FormRequest {
    public function authorize():bool{return true;}
    public function rules():array{return ['status'=>['nullable','string',Rule::in(['ACTIVE','ARCHIVED','ALL'])],'search'=>['nullable','string','max:150'],'merchant_uuid'=>['nullable','uuid'],'per_page'=>['nullable','integer','min:1','max:100'],'page'=>['nullable','integer','min:1']];}
}
