<?php

namespace App\Http\Requests\Integration;

use Illuminate\Foundation\Http\FormRequest;

class LookupPusoPayReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'externalReference' => [
                'bail', 'required_without:receiptId', 'prohibits:receiptId',
                'string', 'max:100',
            ],
            'receiptId' => [
                'bail', 'required_without:externalReference', 'prohibits:externalReference',
                'uuid',
            ],
        ];
    }
}
