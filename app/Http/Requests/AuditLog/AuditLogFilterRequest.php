<?php
namespace App\Http\Requests\AuditLog;
use App\Models\AuditLog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class AuditLogFilterRequest extends FormRequest {public function authorize():bool{return true;}public function rules():array{return ['event_type'=>['nullable',Rule::in([AuditLog::EVENT_PAYSLIP,AuditLog::EVENT_EMPLOYEE,AuditLog::EVENT_TRANSACTION,AuditLog::EVENT_ACKNOWLEDGEMENT])],'per_page'=>['nullable','integer','min:1','max:100']];}}
