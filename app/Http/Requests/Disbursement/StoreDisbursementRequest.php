<?php
namespace App\Http\Requests\Disbursement;
use App\Models\Disbursement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreDisbursementRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['program_type'=>['required','string','max:100'],'program_name'=>['required','string','max:255'],'note'=>['nullable','string','max:2000'],'release_policy'=>['required',Rule::in([Disbursement::POLICY_IMMEDIATE,Disbursement::POLICY_AFTER_ACKNOWLEDGEMENT])],'beneficiaries'=>['required','array','min:1','max:500'],'beneficiaries.*.beneficiary_name'=>['required','string','max:255'],'beneficiaries.*.amount_minor_units'=>['required','integer','min:1'],'beneficiaries.*.wallet_uuid'=>['nullable','uuid'],'beneficiaries.*.email'=>['nullable','email','max:255'],'beneficiaries.*.reference_no'=>['nullable','string','max:255'],'beneficiaries.*.purpose'=>['nullable','string','max:255']];}
}
