<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StatusResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_type' => $this->category_type,
            'code' => $this->code,
            'name' => $this->name,
            'color' => $this->color,
            'display_order' => $this->display_order,
            'is_default' => $this->is_default,
            'is_active' => $this->is_active,
        ];
    }
}
