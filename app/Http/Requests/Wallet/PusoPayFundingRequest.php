<?php

namespace App\Http\Requests\Wallet;

use Illuminate\Foundation\Http\FormRequest;

class PusoPayFundingRequest extends FormRequest
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
                'max:100',
            ],

            'externalReference' => [
                'required',
                'string',
                'max:150',
            ],

            'amountMinorUnits' => [
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
        ];
    }
}