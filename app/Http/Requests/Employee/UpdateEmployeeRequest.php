<?php

namespace App\Http\Requests\Employee;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employee = $this->route('uuid');

        $employeeModel = Employee::where('uuid', $employee)->first();

        return [

            'employee_no' => [
                'sometimes',
                'string',
                'max:50',
            ],

            'first_name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'sometimes',
                'string',
                'max:100',
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
            ],

            'mobile' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],

            'position' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'department' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'pusopay_wallet_id' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('employees', 'pusopay_wallet_id')
                    ->ignore($employeeModel?->id),
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    Employee::STATUS_ACTIVE,
                    Employee::STATUS_INACTIVE,
                    Employee::STATUS_SUSPENDED,
                    Employee::STATUS_TERMINATED,
                ]),
            ],

            'hired_at' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'terminated_at' => [
                'sometimes',
                'nullable',
                'date',
                'after_or_equal:hired_at',
            ],
        ];
    }
}