<?php

namespace App\Http\Resources;

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
            'status' => $this->status,
            'current_status' => StatusResource::make($this->whenLoaded('currentStatus')),
            'status_name' => $this->status_name,
            'status_color' => $this->status_color,
            'payment_status' => $this->payment_status,
            'payment_type' => $this->payment_type,
            'date' => $this->date?->toDateString(),
            'subtotal' => (float) $this->subtotal,
            'sub_total' => (float) $this->sub_total,
            'tax_amount' => (float) $this->tax_amount,
            'vat' => (float) $this->vat,
            'discount' => (float) $this->discount,
            'discount_type' => $this->discount_type,
            'discount_percent' => (float) $this->discount_percent,
            'total' => (float) $this->total,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'loyalty_points_earned' => $this->loyalty_points_earned,
            'loyalty_points_redeemed' => $this->loyalty_points_redeemed,
            'loyalty_discount' => (float) $this->loyalty_discount,
            'customer_name' => $this->customer_name,
            'cashier_name' => $this->cashier_name,
            'notes' => $this->notes,
            'is_completed' => $this->is_completed,
            'is_cancelled' => $this->is_cancelled,
            'is_paid' => $this->is_paid,
            'can_be_cancelled' => $this->can_be_cancelled,
            'can_be_refunded' => $this->can_be_refunded,
            'item_count' => $this->item_count,
            'completed_at' => $this->completed_at?->toISOString(),
            'store_id' => $this->store_id,
            'store' => $this->whenLoaded('store'),
            'customer_id' => $this->customer_id,
            'customer' => CustomerResource::make($this->whenLoaded('customer')),
            'user_id' => $this->user_id,
            'user' => UserResource::make($this->whenLoaded('user')),
            'created_by' => UserResource::make($this->whenLoaded('createdBy')),
            'updated_by' => UserResource::make($this->whenLoaded('updatedBy')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'status_histories' => StatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
