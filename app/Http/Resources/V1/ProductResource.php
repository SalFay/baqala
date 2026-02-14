<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $storeId = $request->get('store_id');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'display_name' => $this->display_name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'type' => $this->type?->value,
            'type_label' => $this->type?->label(),
            'description' => $this->description,
            'image' => $this->image,
            'image_url' => $this->image_url,
            'cost_price' => (float) $this->cost_price,
            'sale_price' => (float) $this->sale_price,
            'compare_price' => $this->compare_price ? (float) $this->compare_price : null,
            'has_discount' => $this->has_discount,
            'discount_percent' => $this->discount_percent,
            'profit' => $this->profit,
            'profit_margin' => $this->profit_margin,
            'track_inventory' => $this->track_inventory,
            'low_stock_threshold' => $this->low_stock_threshold,
            'stock_quantity' => $this->when(
                $this->track_inventory,
                fn() => $this->getStockQuantity($storeId)
            ),
            'is_in_stock' => $this->when(
                $this->track_inventory,
                fn() => $this->isInStock($storeId)
            ),
            'is_low_stock' => $this->when(
                $this->track_inventory,
                fn() => $this->isLowStock($storeId)
            ),
            'weight' => $this->weight ? (float) $this->weight : null,
            'weight_unit' => $this->weight_unit,
            'is_active' => $this->is_active,
            'is_variable' => $this->is_variable,
            'meta' => $this->meta,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'code' => $this->category->code,
            ]),
            'vendor' => $this->whenLoaded('vendor', fn() => $this->vendor ? [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
            ] : null),
            'variants' => $this->whenLoaded('variants', fn() => $this->variants->map(fn($v) => [
                'id' => $v->id,
                'name' => $v->name,
                'sku' => $v->sku,
                'barcode' => $v->barcode,
                'cost_price' => (float) $v->cost_price,
                'sale_price' => (float) $v->sale_price,
                'stock_quantity' => $this->when(
                    $this->track_inventory,
                    fn() => $v->getStockQuantity($storeId)
                ),
            ])),
        ];
    }
}
