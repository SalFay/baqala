<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @param $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'arabic_name' => $this->arabic_name,
            'pid' => $this->pid,
            'category_id' => $this->category_id,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'), // Format the date if needed
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'), // Format the date if needed
            'deleted_at' => $this->deleted_at, // Can be null, if soft deleted
            'purchase_price' => $this->purchase_price,
            'sale_price' => $this->sale_price,
            'status' => $this->status,
            'taxable' => $this->taxable,
            'taxable_price' => $this->taxable_price,
            'product_image' => $this->image, // Use placeholder image if no image is available
            'image' => $this->image, // Use placeholder image if no image is available
            'category' => $this->category,
        ];
    }
}
