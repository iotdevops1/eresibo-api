<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status' => [
                'id' => $this->status,
                'name' => match ($this->status) {
                    1 => 'ACTIVE',
                    2 => 'INACTIVE',
                    3 => 'SUSPENDED',
                    4 => 'LOCKED',
                    5 => 'DELETED',
                    default => 'UNKNOWN',
                },
            ],
            'role' => $this->whenLoaded('role',fn () => [
                    'code' => $this->role->code,
                    'name' => $this->role->name,
                ]
            ),
            'merchant' => $this->whenLoaded(
                'merchant',
                fn () => [
                    'uuid' => $this->merchant->uuid,
                    'merchant_code' => $this->merchant->merchant_code,
                    'business_name' => $this->merchant->business_name,
                ]
            ),

            'is_login' => (bool) $this->is_login,

            'last_login_at' => $this->last_login_at?->toISOString(),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}