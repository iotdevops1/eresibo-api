<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PayrollBatchItemResource;

class PayrollBatchItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,

            'employee' => $this->whenLoaded(
                'employee',
                function () {
                    return [
                        'uuid' => $this->employee->uuid,
                        'employee_no' => $this->employee->employee_no,
                        'name' => $this->employee->full_name,
                        'pusopay_wallet_id' => $this->employee->pusopay_wallet_id,
                    ];
                }
            ),

            'amounts' => [
                'gross' => (float) $this->gross_amount,
                'deduction' => (float) $this->deduction_amount,
                'net' => (float) $this->net_amount,
            ],

            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    1 => 'PENDING',
                    2 => 'PROCESSING',
                    3 => 'COMPLETED',
                    4 => 'FAILED',
                    5 => 'CANCELLED',
                    default => 'UNKNOWN',
                },
            ],

            'payslip_id' => $this->payslip_id,

            'payout' => [
                'status' => $this->payout_status,
                'reference' => $this->payout_reference,
            ],

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}