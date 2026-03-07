<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CouponResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_amount' => (float) $this->discount_amount,
            'discount_display' => $this->discount_display,
            'applies_to' => $this->applies_to,
            'applies_to_ids' => $this->applies_to_ids,
            'min_order_amount' => (float) $this->min_order_amount,
            'max_discount_amount' => $this->max_discount_amount ? (float) $this->max_discount_amount : null,
            'customer_ids' => $this->customer_ids,
            'customer_group_ids' => $this->customer_group_ids,
            'first_order_only' => (bool) $this->first_order_only,
            'max_uses' => $this->max_uses,
            'max_uses_per_customer' => $this->max_uses_per_customer,
            'current_uses' => $this->current_uses,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'is_valid' => $this->isValid(),
            'is_active' => (bool) $this->is_active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
