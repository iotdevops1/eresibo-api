<?php

namespace App\Http\Resources;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifiedDocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        // Keep this public response an explicit allowlist, never a full model dump.
        if ($this->resource instanceof Payslip) {
            return [
                'document_type' => 'Payslip',
                'reference' => 'PAYSLIP-'.$this->uuid,
                'source_system' => 'ERESIBO',
                'issuer' => $this->merchant?->business_name,
                'status' => $this->status === Payslip::STATUS_ACKNOWLEDGED ? 'Acknowledged' : 'Pending acknowledgement',
                'issued_at' => $this->created_at?->toISOString(),
                'pay_date' => $this->pay_date?->format('Y-m-d'),
                'amount_minor' => $this->net_amount_minor_units,
                'currency' => $this->currency,
                'acknowledged_at' => $this->acknowledged_at?->toISOString(),
            ];
        }

        return [
            'document_type' => 'Receipt',
            'reference' => $this->external_reference,
            'receipt_id' => $this->uuid,
            'source_system' => $this->source_system,
            'status' => 'Confirmed',
            'issued_at' => $this->occurred_at?->toISOString(),
            'amount_minor' => $this->amount_minor,
            'currency' => $this->currency,
            'transaction_type' => $this->transaction_type,
            'expires_at' => $this->expires_at?->toISOString(),
        ];
    }
}
