<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'store_id' => $this->store_id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity ?? 0,
            'available_quantity' => $this->quantity - ($this->reserved_quantity ?? 0),
            'low_stock_threshold' => $this->low_stock_threshold ?? $this->product?->low_stock_threshold,
            'is_low_stock' => $this->quantity <= ($this->low_stock_threshold ?? $this->product?->low_stock_threshold ?? 0),
            'last_counted_at' => $this->last_counted_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'store' => $this->whenLoaded('store', fn() => new StoreResource($this->store)),
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'name_ar' => $this->product->name_ar,
                'sku' => $this->product->sku,
                'barcode' => $this->product->barcode,
                'image_url' => $this->product->image_url,
                'cost_price' => (float) $this->product->cost_price,
                'sale_price' => (float) $this->product->sale_price,
            ]),
            'variant' => $this->whenLoaded('variant', fn() => $this->variant ? [
                'id' => $this->variant->id,
                'name' => $this->variant->name,
                'sku' => $this->variant->sku,
                'barcode' => $this->variant->barcode,
            ] : null),
        ];
    }
}
