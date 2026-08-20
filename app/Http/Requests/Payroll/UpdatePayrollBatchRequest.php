<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePayrollBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Batch
            |--------------------------------------------------------------------------
            */

            'batch_no' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'pay_period_start' => [
                'sometimes',
                'date',
            ],

            'pay_period_end' => [
                'sometimes',
                'date',
                'after_or_equal:pay_period_start',
            ],

            'pay_date' => [
                'sometimes',
                'date',
                'after_or_equal:pay_period_end',
            ],

            'description' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            |
            | Optional during draft editing.
            |
            */

            'items' => [
                'sometimes',
                'array',
                'min:1',
            ],

            'items.*.employee_id' => [
                'required_with:items',
                'integer',
                'distinct',
                'exists:employees,id',
            ],

            'items.*.gross_amount' => [
                'required_with:items',
                'numeric',
                'min:0.01',
                'decimal:0,2',
            ],

            'items.*.deduction_amount' => [
                'required_with:items',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'items.min'                              => 'At least one employee is required.',
            'items.*.employee_id.required_with'      => 'Employee is required.',
            'items.*.employee_id.distinct'           => 'An employee can only appear once in a payroll batch.',
            'items.*.employee_id.exists'             => 'The selected employee does not exist.',
            'items.*.gross_amount.required_with'     => 'Gross amount is required.',
            'items.*.deduction_amount.required_with' => 'Deduction amount is required.',
        ];
    }
}