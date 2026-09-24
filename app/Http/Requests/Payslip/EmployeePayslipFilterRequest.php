<?php
namespace App\Http\Requests\Payslip;

use App\Models\Payslip;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePayslipFilterRequest extends FormRequest {
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'status'=>['nullable','integer',Rule::in([
                Payslip::STATUS_PENDING_ACKNOWLEDGEMENT,
                Payslip::STATUS_ACKNOWLEDGED,
            ])],
            'pay_date'=>['nullable','date'],
            'per_page'=>['nullable','integer','min:1','max:100'],
            'page'=>['nullable','integer','min:1'],
        ];
    }
}
