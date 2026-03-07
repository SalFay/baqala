<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'order_item_id' => $this->order_item_id,
            'order_number' => $this->order?->order_number,
            'product_name' => $this->orderItem?->product?->name ?? 'Unknown',
            'quantity' => $this->orderItem?->quantity ?? 1,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'station' => $this->station,
            'priority' => $this->priority,
            'priority_color' => $this->priority_color,
            'notes' => $this->notes,
            'waiting_time' => $this->waiting_time,
            'started_at' => $this->started_at?->format('H:i'),
            'completed_at' => $this->completed_at?->format('H:i'),
            'prepared_by' => $this->preparedByUser?->name,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
