<?php

namespace App\Http\Requests\Dispute;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployerDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:150'],
            'description' => ['required', 'string', 'max:5000'],
            'employee_uuid' => ['nullable', 'uuid'],
            'payslip_uuid' => ['nullable', 'uuid'],
            'priority' => ['nullable', 'integer', Rule::in([
                Dispute::PRIORITY_LOW,
                Dispute::PRIORITY_NORMAL,
                Dispute::PRIORITY_HIGH,
            ])],
        ];
    }
}
