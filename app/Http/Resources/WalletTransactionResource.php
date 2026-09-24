<?php

namespace App\Http\Resources;

use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'reference' => $this->reference,
            'type' => $this->type,
            'direction' => $this->transaction_direction,
            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    WalletTransaction::STATUS_PENDING => 'PENDING',
                    WalletTransaction::STATUS_COMPLETED => 'COMPLETED',
                    WalletTransaction::STATUS_FAILED => 'FAILED',
                    WalletTransaction::STATUS_CANCELLED => 'CANCELLED',
                    default => 'UNKNOWN',
                },
            ],
            'amount_minor_units' => $this->amount_minor_units,
            'amount_major_units' => $this->amount_major_units,
            'currency' => $this->currency,
            'description' => $this->description,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
