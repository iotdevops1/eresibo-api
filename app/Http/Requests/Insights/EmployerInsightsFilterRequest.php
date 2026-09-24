<?php

namespace App\Http\Requests\Insights;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployerInsightsFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'range' => ['nullable', 'string', Rule::in([
                'LAST_30_DAYS',
                'LAST_90_DAYS',
                'LAST_6_MONTHS',
                'LAST_12_MONTHS',
            ])],
        ];
    }
}
