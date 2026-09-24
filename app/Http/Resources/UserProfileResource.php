<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'locale' => $this->preference?->locale ?? 'en',
            'role' => $this->whenLoaded('role', fn () => [
                'code' => $this->role?->code,
                'name' => $this->role?->name,
            ]),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
