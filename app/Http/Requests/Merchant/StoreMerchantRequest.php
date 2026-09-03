<?php

namespace App\Http\Requests\Merchant;

use App\Models\Merchant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'merchant_code' => [
                'required',
                'string',
                'max:50',
                'unique:merchants,merchant_code',
            ],

            'business_name' => [
                'required',
                'string',
                'max:255',
            ],

            'business_type' => [
                'nullable',
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

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'required',
                Rule::in([
                    Merchant::STATUS_ACTIVE,
                    Merchant::STATUS_INACTIVE,
                    Merchant::STATUS_SUSPENDED,
                ]),
            ],
        ];
    }
}