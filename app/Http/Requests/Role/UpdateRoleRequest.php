<?php

namespace App\Http\Requests\Role;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $uuid = $this->route('uuid');

        return [
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('user_roles', 'code')
                    ->ignore($uuid, 'uuid'),
            ],

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'active' => [
                'required',
                'boolean',
            ],
        ];
    }
}