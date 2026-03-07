<?php

namespace App\Http\Requests\Api\TimePricing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTimePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['sometimes', 'in:percentage,fixed,special_price'],
            'discount_value' => ['sometimes', 'numeric', 'min:0'],
            'applies_to' => ['sometimes', 'in:all,products,categories,brands'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'min:1', 'max:7'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }
}
