<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'postal_code' => $this->postal_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_main' => (bool) $this->is_main,
            'is_active' => (bool) $this->is_active,
            'selling_price_group_id' => $this->selling_price_group_id,
            'selling_price_group' => $this->whenLoaded('sellingPriceGroup', fn() => $this->sellingPriceGroup?->name),
            'invoice_prefix' => $this->invoice_prefix,
            'settings' => $this->settings,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
