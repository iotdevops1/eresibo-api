<?php

namespace App\Http\Requests\Settings;

use App\Models\UserPreference;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateReceiptPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'receipt_generation_mode' => ['sometimes', Rule::in([
                UserPreference::RECEIPT_MODE_ALWAYS,
                UserPreference::RECEIPT_MODE_ASK,
                UserPreference::RECEIPT_MODE_NEVER,
            ])],
            'default_receipt_type' => ['sometimes', Rule::in([
                UserPreference::RECEIPT_TYPE_SIMPLE_PROOF,
                UserPreference::RECEIPT_TYPE_ERECEIPT,
                UserPreference::RECEIPT_TYPE_OFFICIAL_ERESIBO,
            ])],
            'locale' => ['sometimes', Rule::in(['en', 'fil'])],
        ];
    }
}
