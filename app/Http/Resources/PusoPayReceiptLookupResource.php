<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class PusoPayReceiptLookupResource extends PusoPayReceiptResource
{
    public function toArray(Request $request): array
    {
        return array_merge(parent::toArray($request), [
            'amountMinor' => $this->amount_minor,
            'currency' => $this->currency,
            'transactionType' => $this->transaction_type,
            'counterpartyLabel' => $this->counterparty_label,
            'occurredAt' => $this->occurred_at?->toISOString(),
            'isExpired' => $this->expires_at?->isPast() ?? true,
        ]);
    }
}
