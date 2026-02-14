<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'status' => $this->status,
            'hold_name' => $this->hold_name,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount' => (float) $this->discount,
            'discount_type' => $this->discount_type,
            'discount_reason' => $this->discount_reason,
            'total' => (float) $this->total,
            'loyalty_points_to_redeem' => $this->loyalty_points_to_redeem,
            'loyalty_discount' => (float) $this->loyalty_discount,
            'notes' => $this->notes,
            'item_count' => $this->item_count,
            'total_items' => $this->total_items,
            'has_discount' => $this->has_discount,
            'total_discount' => (float) $this->total_discount,
            'is_held' => $this->is_held,
            'held_at' => $this->held_at?->toISOString(),
            'expires_at' => $this->expires_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'customer' => $this->whenLoaded('customer', fn() => new CustomerResource($this->customer)),
            'items' => $this->whenLoaded('items', fn() => CartItemResource::collection($this->items)),
        ];
    }
}
