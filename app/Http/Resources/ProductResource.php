<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'display_name' => $this->display_name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'type' => $this->type,
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'cost_price' => (float) $this->cost_price,
            'sale_price' => (float) $this->sale_price,
            'compare_price' => (float) $this->compare_price,
            'has_discount' => $this->has_discount,
            'discount_percent' => $this->discount_percent,
            'profit' => $this->profit,
            'profit_margin' => $this->profit_margin,
            'track_inventory' => $this->track_inventory,
            'low_stock_threshold' => $this->low_stock_threshold,
            'weight' => $this->weight,
            'weight_unit' => $this->weight_unit,
            'is_active' => $this->is_active,
            'is_variable' => $this->is_variable,
            'meta' => $this->meta,
            'category_id' => $this->category_id,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'vendor_id' => $this->vendor_id,
            'vendor' => VendorResource::make($this->whenLoaded('vendor')),
            'store_id' => $this->store_id,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'store_inventories' => StoreInventoryResource::collection($this->whenLoaded('storeInventories')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
