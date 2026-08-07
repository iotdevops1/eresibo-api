<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [

            'role_code' => [
                'required',
                'exists:user_roles,code',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'status' => [
                'required',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                    'SUSPENDED',
                    'LOCKED',
                ]),
            ],

        ];
    }
}