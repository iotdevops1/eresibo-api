<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePusoPayReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'externalReference' => [
                'required',
                'string',
                'max:100',
            ],

            'amountMinor' => [
                'required',
                'integer',
                'min:0',
            ],

            'currency' => [
                'required',
                'string',
                'size:3',
            ],

            'transactionType' => [
                'required',
                Rule::in([
                    'CASH_OUT',
                    'TRANSFER',
                    'BILL_PAYMENT',
                    'MERCHANT_PAYMENT',
                    'CASH_IN',
                ]),
            ],

            'counterpartyLabel' => [
                'nullable',
                'string',
                'max:255',
            ],

            'occurredAt' => [
                'required',
                'date',
            ],
        ];
    }
}