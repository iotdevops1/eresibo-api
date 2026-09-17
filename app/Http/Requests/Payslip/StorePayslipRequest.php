<?php
namespace App\Http\Requests\Payslip;
use Illuminate\Foundation\Http\FormRequest;
class StorePayslipRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array { return [
        'employee_no'=>['required','string','max:50'],
        'pay_period_start'=>['required','date'],
        'pay_period_end'=>['required','date','after_or_equal:pay_period_start'],
        'pay_date'=>['required','date','after_or_equal:pay_period_end'],
        'note'=>['nullable','string','max:255'],
        'earnings'=>['required','array','min:1'],
        'earnings.*.description'=>['required','string','max:100'],
        'earnings.*.amount_minor_units'=>['required','integer','min:1'],
        'deductions'=>['nullable','array'],
        'deductions.*.description'=>['required','string','max:100'],
        'deductions.*.amount_minor_units'=>['required','integer','min:1'],
    ]; }
}
