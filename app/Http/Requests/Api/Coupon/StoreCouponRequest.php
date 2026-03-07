<?php

namespace App\Http\Requests\Api\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', 'unique:coupons,code'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['required', 'in:fixed,percentage,free_shipping'],
            'discount_amount' => ['required_unless:discount_type,free_shipping', 'nullable', 'numeric', 'min:0.01'],
            'applies_to' => ['required', 'in:all,category,brand,product'],
            'applies_to_ids' => ['nullable', 'array'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'customer_ids' => ['nullable', 'array'],
            'customer_group_ids' => ['nullable', 'array'],
            'first_order_only' => ['boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['boolean'],
        ];
    }
}
