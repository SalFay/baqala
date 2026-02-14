<?php

namespace App\Http\Requests\Api\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'unique:products,sku,' . $productId],
            'barcode' => ['nullable', 'string'],
            'type' => ['sometimes', 'in:simple,variable'],
            'category_id' => ['sometimes', 'exists:categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'description' => ['nullable', 'string'],
            'cost_price' => ['sometimes', 'numeric', 'min:0'],
            'sale_price' => ['sometimes', 'numeric', 'min:0'],
            'compare_price' => ['nullable', 'numeric', 'min:0'],
            'track_inventory' => ['boolean'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'weight_unit' => ['nullable', 'string', 'in:kg,g,lb,oz'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'meta' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'The selected category does not exist.',
            'sku.unique' => 'This SKU is already in use.',
        ];
    }
}
