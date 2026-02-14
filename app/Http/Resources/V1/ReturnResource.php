<?php

namespace App\Http\Resources\V1;

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
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'restocking_fee' => (float) ($this->restocking_fee ?? 0),
            'total' => (float) $this->total,
            'refund_amount' => (float) ($this->refund_amount ?? $this->total),
            'refund_method' => $this->refund_method,
            'notes' => $this->notes,
            'processed_at' => $this->processed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'order' => $this->whenLoaded('order', fn() => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'total' => (float) $this->order->total,
                'created_at' => $this->order->created_at?->toISOString(),
            ]),
            'reason' => $this->whenLoaded('reason', fn() => $this->reason ? [
                'id' => $this->reason->id,
                'name' => $this->reason->name,
            ] : null),
            'items' => $this->whenLoaded('items', fn() => $this->items->map(fn($item) => [
                'id' => $item->id,
                'order_item_id' => $item->order_item_id,
                'product_name' => $item->product_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
                'condition' => $item->condition,
                'reason' => $item->reason,
                'restocked' => $item->restocked ?? false,
            ])),
            'processed_by' => $this->whenLoaded('processedBy', fn() => $this->processedBy ? [
                'id' => $this->processedBy->id,
                'name' => $this->processedBy->name ?? $this->processedBy->first_name,
            ] : null),
        ];
    }
}
