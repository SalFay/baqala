<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_variant_id' => $this->product_variant_id,
            'sku' => $this->sku,
            'product_name' => $this->product_name,
            'variant_name' => $this->variant_name,
            'display_name' => $this->display_name ?? ($this->product_name . ($this->variant_name ? ' - ' . $this->variant_name : '')),
            'quantity' => $this->quantity ?? $this->stock,
            'unit_price' => (float) ($this->unit_price ?? $this->sale_price),
            'cost_price' => (float) ($this->cost_price ?? $this->purchase_price),
            'discount' => (float) $this->discount,
            'discount_percent' => (float) $this->discount_percent,
            'tax_rate' => (float) $this->tax_rate,
            'tax_amount' => (float) $this->tax_amount,
            'line_total' => (float) $this->line_total,

            // Relationships
            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'sku' => $this->product->sku,
                'barcode' => $this->product->barcode,
                'image_url' => $this->product->image_url,
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
