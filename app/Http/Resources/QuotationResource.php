<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quotation_number' => $this->quotation_number,
            'customer_id' => $this->customer_id,
            'customer_name' => $this->guest_name,
            'customer_email' => $this->customer_email ?? $this->customer?->email,
            'customer_phone' => $this->customer_phone ?? $this->customer?->phone,
            'location_id' => $this->location_id,
            'location' => $this->whenLoaded('location', fn() => $this->location?->name),
            'valid_until' => $this->valid_until?->toDateString(),
            'is_expired' => $this->is_expired,
            'status' => $this->status,
            'status_color' => $this->status_color,
            'subtotal' => (float) $this->subtotal,
            'tax_amount' => (float) $this->tax_amount,
            'discount_amount' => (float) $this->discount_amount,
            'discount_percent' => (float) $this->discount_percent,
            'total' => (float) $this->total,
            'notes' => $this->notes,
            'terms_and_conditions' => $this->terms_and_conditions,
            'items_count' => $this->when($this->relationLoaded('items'), fn() => $this->items->count()),
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'converted_order_id' => $this->converted_order_id,
            'converted_at' => $this->converted_at?->toISOString(),
            'created_by' => $this->whenLoaded('createdBy', fn() => $this->createdBy?->name),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
