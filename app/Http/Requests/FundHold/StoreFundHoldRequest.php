<?php
namespace App\Http\Requests\FundHold;
use Illuminate\Foundation\Http\FormRequest;
class StoreFundHoldRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['destination_wallet_uuid'=>['required','uuid'],'amount_minor_units'=>['required','integer','min:1'],'source_type'=>['required','string','max:100'],'description'=>['nullable','string','max:1000'],'scheduled_release_at'=>['nullable','date','after:now']];}}
