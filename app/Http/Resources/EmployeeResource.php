<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'employee_no' => $this->employee_no,
            'name' => [
                'first' => $this->first_name,
                'middle' => $this->middle_name,
                'last' => $this->last_name,
                'full' => $this->full_name,
            ],
            'email' => $this->email,
            'mobile' => $this->mobile,
            'position' => $this->position,
            'department' => $this->department,
            'pusopay_wallet_id' => $this->pusopay_wallet_id,
            'status' => [
                'id' => $this->status,
                'name' => $this->status_name,
            ],
            'hired_at' => $this->hired_at?->format('Y-m-d'),
            'terminated_at' => $this->terminated_at?->format('Y-m-d'),
            'merchant' => $this->whenLoaded('merchant', fn () => [
                    'uuid' => $this->merchant->uuid,
                    'merchant_code' => $this->merchant->merchant_code,
                    'business_name' => $this->merchant->business_name,
                ]
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}