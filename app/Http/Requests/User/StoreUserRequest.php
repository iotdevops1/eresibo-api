<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'role' => [
                'required',
                'exists:user_roles,code',
            ],
            
            'name' => [
                'required',
                'string',
                'max:255'
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email'
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20'
            ],

            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed'
            ],

            'status' => [ 'required',
                Rule::in([
                    'ACTIVE',
                    'INACTIVE',
                    'SUSPENDED',
                    'LOCKED'
                ])
            ],

        ];
    }
}