<?php

namespace App\Http\Requests\Api\TimePricing;

use Illuminate\Foundation\Http\FormRequest;

class StoreTimePricingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['required', 'in:percentage,fixed,special_price'],
            'discount_value' => ['required', 'numeric', 'min:0'],
            'applies_to' => ['required', 'in:all,products,categories,brands'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['exists:products,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['exists:brands,id'],
            'days_of_week' => ['nullable', 'array'],
            'days_of_week.*' => ['integer', 'min:1', 'max:7'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'priority' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'end_time.after' => 'End time must be after start time.',
            'ends_at.after' => 'End date must be after start date.',
            'discount_value.min' => 'Discount value must be at least 0.',
        ];
    }
}
