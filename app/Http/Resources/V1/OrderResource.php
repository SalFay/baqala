<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'invoice_no' => $this->invoice_no,
            'status' => $this->status?->value,
            'status_label' => $this->status?->label(),
            'status_color' => $this->status?->color(),
            'payment_status' => $this->payment_status?->value,
            'payment_status_label' => $this->payment_status?->label(),
            'payment_type' => $this->payment_type,
            'subtotal' => (float) ($this->sub_total ?? $this->subtotal),
            'tax_amount' => (float) $this->tax_amount,
            'discount' => (float) $this->discount,
            'discount_type' => $this->discount_type,
            'total' => (float) $this->total,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'loyalty_points_earned' => $this->loyalty_points_earned,
            'loyalty_points_redeemed' => $this->loyalty_points_redeemed,
            'loyalty_discount' => (float) $this->loyalty_discount,
            'customer_name' => $this->customer_name,
            'cashier_name' => $this->cashier_name,
            'notes' => $this->notes,
            'item_count' => $this->item_count,
            'can_be_cancelled' => $this->can_be_cancelled,
            'can_be_refunded' => $this->can_be_refunded,
            'completed_at' => $this->completed_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'store' => $this->whenLoaded('store', fn() => new StoreResource($this->store)),
            'customer' => $this->whenLoaded('customer', fn() => new CustomerResource($this->customer)),
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name ?? $this->user->first_name,
            ]),
            'items' => $this->whenLoaded('items', fn() => OrderItemResource::collection($this->items)),
            'payments' => $this->whenLoaded('payments', fn() => $this->payments->map(fn($p) => [
                'id' => $p->id,
                'amount' => (float) $p->amount,
                'payment_type' => $p->payment_type,
                'reference' => $p->reference,
                'status' => $p->status,
            ])),
            'returns' => $this->whenLoaded('returns', fn() => ReturnResource::collection($this->returns)),
        ];
    }
}
