<?php

namespace App\Http\Requests\Merchant;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEmployerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $employer = $this->route('user');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'email' => [
                'sometimes',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($employer?->id),
            ],

            'mobile' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],

            'password' => [
                'sometimes',
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    User::STATUS_ACTIVE,
                    User::STATUS_INACTIVE,
                    User::STATUS_SUSPENDED,
                    User::STATUS_LOCKED,
                ]),
            ],
        ];
    }
}