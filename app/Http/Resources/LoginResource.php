<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this['access_token'],
            'token_type'   => $this['token_type'],
            'must_change_password' => (bool) ($this->resource['must_change_password'] ?? false),
            'user'         => new UserResource($this['user']),
        ];
    }
}