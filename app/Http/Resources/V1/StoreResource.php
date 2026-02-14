<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'address' => $this->address,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'email' => $this->email,
            'tax_number' => $this->tax_number,
            'currency' => $this->currency ?? 'SAR',
            'timezone' => $this->timezone,
            'is_active' => $this->is_active,
            'is_main' => $this->is_main ?? false,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Statistics (when requested)
            'stats' => $this->when($request->get('include_stats'), fn() => [
                'total_products' => $this->products()->count(),
                'total_orders' => $this->orders()->count(),
                'total_customers' => $this->customers()->count(),
            ]),
        ];
    }
}
