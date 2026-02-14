<?php

namespace App\Http\Requests\Api\V1\Product;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;
use App\Http\Responses\ApiResponse;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'barcode' => 'nullable|string|max:100',
            'type' => ['nullable', new Enum(ProductType::class)],
            'category_id' => 'nullable|integer|exists:categories,id',
            'vendor_id' => 'nullable|integer|exists:vendors,id',
            'store_id' => 'nullable|integer|exists:stores,id',
            'description' => 'nullable|string|max:2000',
            'cost_price' => 'required|numeric|min:0|max:9999999.99',
            'sale_price' => 'required|numeric|min:0|max:9999999.99',
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
            'cost_price.required' => 'Cost price is required',
            'cost_price.min' => 'Cost price cannot be negative',
            'sale_price.required' => 'Sale price is required',
            'sale_price.min' => 'Sale price cannot be negative',
            'category_id.exists' => 'Selected category does not exist',
            'vendor_id.exists' => 'Selected vendor does not exist',
            'image.max' => 'Image size cannot exceed 2MB',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $costPrice = $this->input('cost_price', 0);
            $salePrice = $this->input('sale_price', 0);

            if ($salePrice < $costPrice) {
                $validator->errors()->add('sale_price', 'Sale price should not be less than cost price');
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
