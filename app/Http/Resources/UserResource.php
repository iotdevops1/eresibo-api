<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\UserRoleResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'status' => $this->status,
            'is_login' => $this->is_login,
            'last_login_at' => $this->last_login_at?->toIso8601String(),
            'role' => new UserRoleResource($this->whenLoaded('role')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}