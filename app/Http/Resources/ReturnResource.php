<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'return_number' => $this->return_number,
            'order_id' => $this->order_id,
            'order' => $this->whenLoaded('order', fn() => [
                'id' => $this->order?->id,
                'order_number' => $this->order?->order_number,
            ]),
            'customer_id' => $this->customer_id,
            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer?->id,
                'full_name' => $this->customer?->full_name,
            ]),
            'type' => $this->type,
            'status' => $this->status,
            'return_reason_id' => $this->return_reason_id,
            'return_reason' => $this->whenLoaded('returnReason', fn() => [
                'id' => $this->returnReason?->id,
                'name' => $this->returnReason?->name,
            ]),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'subtotal' => (float) $this->subtotal,
            'restocking_fee' => (float) $this->restocking_fee,
            'refund_amount' => (float) $this->refund_amount,
            'refund_method' => $this->refund_method,
            'processed_by' => $this->whenLoaded('processedBy', fn() => $this->processedBy?->name),
            'processed_at' => $this->processed_at?->toISOString(),
            'items' => $this->whenLoaded('items'),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
