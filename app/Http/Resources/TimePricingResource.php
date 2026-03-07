<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimePricingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'discount_type' => $this->discount_type,
            'discount_value' => (float) $this->discount_value,
            'discount_description' => $this->getDiscountDescription(),
            'applies_to' => $this->applies_to,
            'product_ids' => $this->product_ids,
            'category_ids' => $this->category_ids,
            'brand_ids' => $this->brand_ids,
            'days_of_week' => $this->days_of_week,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'starts_at' => $this->starts_at?->toISOString(),
            'ends_at' => $this->ends_at?->toISOString(),
            'schedule_description' => $this->getScheduleDescription(),
            'priority' => $this->priority,
            'is_active' => $this->is_active,
            'is_active_now' => $this->isActiveNow(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
