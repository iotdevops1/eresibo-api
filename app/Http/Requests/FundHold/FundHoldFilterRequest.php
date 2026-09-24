<?php
namespace App\Http\Requests\FundHold;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class FundHoldFilterRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['status'=>['nullable',Rule::in(['HELD','RELEASED','RETURNED','ALL'])],'search'=>['nullable','string','max:255'],'per_page'=>['nullable','integer','min:1','max:100']];}}
