<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Receipt;

class PusoPayReceiptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'receiptId' => $this->uuid,

            'externalReference' =>
                $this->external_reference,

            'receiptUrl' =>
                $this->public_url,

            'status' => [
                'id' => $this->status,

                'name' => match ($this->status) {
                    Receipt::STATUS_CONFIRMED => 'CONFIRMED',
                    Receipt::STATUS_FAILED => 'FAILED',
                    default => 'UNKNOWN',
                },
            ],

            'expiresAt' =>
                $this->expires_at?->toISOString(),
        ];
    }
}