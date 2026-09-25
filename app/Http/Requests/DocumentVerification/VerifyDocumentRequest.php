<?php

namespace App\Http\Requests\DocumentVerification;

use Illuminate\Foundation\Http\FormRequest;

class VerifyDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => ['required', 'string', 'max:100'],
        ];
    }
}
