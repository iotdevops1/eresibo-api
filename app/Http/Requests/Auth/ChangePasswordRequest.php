<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
       return true;
    }

    public function rules(): array
    {
        return [
            'currentPassword' => [
                'required',
                'string',
            ],

            'newPassword' => [
                'required',
                'string',
                'confirmed',
                Password::min(8),
            ],
        ];
    }
}