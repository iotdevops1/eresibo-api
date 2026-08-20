<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'employee_no' => [
                'required',
                'string',
                'max:50',
            ],

            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'position' => [
                'nullable',
                'string',
                'max:150',
            ],

            'department' => [
                'nullable',
                'string',
                'max:150',
            ],

            'pusopay_wallet_id' => [
                'required',
                'string',
                'max:100',
                'unique:employees,pusopay_wallet_id',
            ],

            'status' => [
                'required',
                Rule::in([
                    Employee::STATUS_ACTIVE,
                    Employee::STATUS_INACTIVE,
                    Employee::STATUS_SUSPENDED,
                    Employee::STATUS_TERMINATED,
                ]),
            ],

            'hired_at' => [
                'nullable',
                'date',
            ],
        ];
    }
}