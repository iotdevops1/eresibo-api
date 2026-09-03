<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class StorePayrollBatchRequest extends FormRequest
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
            | Payroll Batch
            |--------------------------------------------------------------------------
            */

            'batch_no' => [
                'required',
                'string',
                'max:50',
            ],

            'pay_period_start' => [
                'required',
                'date',
            ],

            'pay_period_end' => [
                'required',
                'date',
                'after_or_equal:pay_period_start',
            ],

            'pay_date' => [
                'required',
                'date',
                'after_or_equal:pay_period_end',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],

            /*
            |--------------------------------------------------------------------------
            | Payroll Employees
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.employee_id' => [
                'required',
                'integer',
                'distinct',
                'exists:employees,id',
            ],

            'items.*.gross_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'decimal:0,2',
            ],

            'items.*.deduction_amount' => [
                'required',
                'numeric',
                'min:0',
                'decimal:0,2',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'items.required' =>
                'At least one employee is required.',

            'items.min' =>
                'At least one employee is required.',

            'items.*.employee_id.required' =>
                'Employee is required.',

            'items.*.employee_id.distinct' =>
                'An employee can only appear once in a payroll batch.',

            'items.*.employee_id.exists' =>
                'The selected employee does not exist.',

            'items.*.gross_amount.required' =>
                'Gross amount is required.',

            'items.*.gross_amount.min' =>
                'Gross amount must be greater than zero.',

            'items.*.deduction_amount.required' =>
                'Deduction amount is required.',
        ];
    }
}