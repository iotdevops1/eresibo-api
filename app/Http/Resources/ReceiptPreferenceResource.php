<?php

namespace App\Http\Resources;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceiptPreferenceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'receipt_generation_mode' => $this->receipt_generation_mode,
            'receipt_generation_mode_label' => match ($this->receipt_generation_mode) {
                UserPreference::RECEIPT_MODE_ALWAYS => 'Always generate',
                UserPreference::RECEIPT_MODE_ASK => 'Ask every time',
                UserPreference::RECEIPT_MODE_NEVER => 'Never generate automatically',
            },
            'default_receipt_type' => $this->default_receipt_type,
            'default_receipt_type_label' => match ($this->default_receipt_type) {
                UserPreference::RECEIPT_TYPE_SIMPLE_PROOF => 'Simple proof of payment',
                UserPreference::RECEIPT_TYPE_ERECEIPT => 'eReceipt',
                UserPreference::RECEIPT_TYPE_OFFICIAL_ERESIBO => 'Official eResibo',
            },
            'locale' => $this->locale,
            'salary_payslips_automatic' => true,
            'salary_payslips_note' => 'Salary payslips and government advices are always created automatically and are not affected by this setting.',
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
