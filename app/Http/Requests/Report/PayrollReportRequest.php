<?php
namespace App\Http\Requests\Report;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class PayrollReportRequest extends FormRequest {
 public function authorize():bool{return true;}
 public function rules():array{return ['start_date'=>['nullable','date'],'end_date'=>['nullable','date','after_or_equal:start_date'],'format'=>['nullable',Rule::in(['xlsx','pdf','csv','json'])]];}
}
