<?php

namespace App\Http\Requests\Api\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $couponId = $this->route('coupon')?->id;

        return [
            'code' => ['sometimes', 'string', 'max:50', 'unique:coupons,code,' . $couponId],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'discount_type' => ['sometimes', 'in:fixed,percentage,free_shipping'],
            'discount_amount' => ['nullable', 'numeric', 'min:0.01'],
            'applies_to' => ['sometimes', 'in:all,category,brand,product'],
            'applies_to_ids' => ['nullable', 'array'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_discount_amount' => ['nullable', 'numeric', 'min:0'],
            'customer_ids' => ['nullable', 'array'],
            'customer_group_ids' => ['nullable', 'array'],
            'first_order_only' => ['boolean'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'max_uses_per_customer' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }
}
