<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class PrefundMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount_minor_units' => [
                'required',
                'integer',
                'min:1',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
                'uppercase',
            ],

            'reference' => [
                'required',
                'string',
                'max:100',
            ],

            'description' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }
}