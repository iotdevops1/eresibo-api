<?php

namespace App\Http\Requests\Merchant;

use App\Models\Merchant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMerchantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $merchant = $this->route('uuid');

        $merchantModel = Merchant::where(
            'uuid',
            $merchant
        )->first();

        return [
            'merchant_code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('merchants', 'merchant_code')
                    ->ignore($merchantModel?->id),
            ],

            'business_name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'business_type' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
            ],

            'mobile' => [
                'sometimes',
                'nullable',
                'string',
                'max:20',
            ],

            'address' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],

            'status' => [
                'sometimes',
                Rule::in([
                    Merchant::STATUS_ACTIVE,
                    Merchant::STATUS_INACTIVE,
                    Merchant::STATUS_SUSPENDED,
                ]),
            ],
        ];
    }
}