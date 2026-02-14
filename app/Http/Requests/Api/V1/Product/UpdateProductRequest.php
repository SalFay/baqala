<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;
use App\Http\Responses\ApiResponse;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $productId,
            'barcode' => 'nullable|string|max:100',
            'type' => ['nullable', new Enum(ProductType::class)],
            'category_id' => 'nullable|integer|exists:categories,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'store_id' => 'nullable|integer|exists:stores,id',
            'description' => 'nullable|string|max:2000',
            'cost_price' => 'sometimes|required|numeric|min:0|max:9999999.99',
            'sale_price' => 'sometimes|required|numeric|min:0|max:9999999.99',
            'compare_price' => 'nullable|numeric|min:0|max:9999999.99',
            'track_inventory' => 'nullable|boolean',
            'low_stock_threshold' => 'nullable|integer|min:0|max:99999',
            'weight' => 'nullable|numeric|min:0|max:99999.999',
            'weight_unit' => 'nullable|string|in:kg,g,lb,oz',
            'is_active' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'meta' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'name.max' => 'Product name cannot exceed 255 characters',
            'sku.unique' => 'This SKU is already in use',
            'cost_price.min' => 'Cost price cannot be negative',
            'sale_price.min' => 'Sale price cannot be negative',
            'category_id.exists' => 'Selected category does not exist',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'image.max' => 'Image size cannot exceed 2MB',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
