<?php
namespace App\Http\Requests\Disbursement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class DisbursementFilterRequest extends FormRequest {
 public function authorize():bool{return true;} public function rules():array{return ['search'=>['nullable','string','max:255'],'program_type'=>['nullable','string','max:100'],'status'=>['nullable',Rule::in(['APPROVED','RELEASED','CANCELLED'])],'per_page'=>['nullable','integer','min:1','max:100']];}
}
