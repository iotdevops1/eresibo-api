<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollBatchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'batch_no' => $this->batch_no,

            'description' => $this->description,

            'pay_period' => [
                'start' => $this->pay_period_start?->format('Y-m-d'),
                'end' => $this->pay_period_end?->format('Y-m-d'),
            ],

            'pay_date' => $this->pay_date?->format('Y-m-d'),

            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    1 => 'DRAFT',
                    2 => 'PROCESSING',
                    3 => 'SUBMITTED',
                    4 => 'PARTIALLY_PROCESSED',
                    5 => 'COMPLETED',
                    6 => 'FAILED',
                    7 => 'CANCELLED',
                    default => 'UNKNOWN',
                },
            ],

            'summary' => [
                'total_employees' => $this->total_employees,
                'total_gross_amount' => (float) $this->total_gross_amount,
                'total_deduction_amount' => (float) $this->total_deduction_amount,
                'total_net_amount' => (float) $this->total_net_amount,
            ],

            'submitted_at' => $this->submitted_at?->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),

            'items' => PayrollBatchItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}