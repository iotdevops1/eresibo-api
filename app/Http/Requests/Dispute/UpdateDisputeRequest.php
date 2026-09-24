<?php

namespace App\Http\Requests\Dispute;

use App\Models\Dispute;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDisputeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['sometimes', 'integer', Rule::in([
                Dispute::STATUS_OPEN,
                Dispute::STATUS_PENDING,
                Dispute::STATUS_RESOLVED,
                Dispute::STATUS_CLOSED,
            ])],
            'priority' => ['sometimes', 'integer', Rule::in([
                Dispute::PRIORITY_LOW,
                Dispute::PRIORITY_NORMAL,
                Dispute::PRIORITY_HIGH,
            ])],
        ];
    }
}
