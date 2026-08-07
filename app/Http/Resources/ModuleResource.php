<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'code' => $this->code,
            'name' => $this->name,
            'icon' => $this->icon,
            'route' => $this->route,
            'children' => ModuleResource::collection(
                $this->whenLoaded('children')
            ),
        ];
    }
}