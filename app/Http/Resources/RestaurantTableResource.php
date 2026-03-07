<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantTableResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity' => (int) $this->capacity,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'section' => $this->section,
            'floor' => $this->floor,
            'shape' => $this->shape,
            'position_x' => $this->position_x,
            'position_y' => $this->position_y,
            'is_active' => (bool) $this->is_active,
            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location', fn() => $this->location?->name),
            'current_order_id' => $this->current_order_id,
            'current_order' => $this->whenLoaded('currentOrder', fn() => [
                'id' => $this->currentOrder?->id,
                'order_number' => $this->currentOrder?->order_number,
            ]),
            'has_order' => !is_null($this->current_order_id),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
