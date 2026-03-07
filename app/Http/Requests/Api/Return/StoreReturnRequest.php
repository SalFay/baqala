<?php

namespace App\Http\Requests\Api\Return;

use Illuminate\Foundation\Http\FormRequest;

class StoreReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'exists:orders,id'],
            'type' => ['required', 'in:refund,exchange,store_credit'],
            'return_reason_id' => ['nullable', 'exists:return_reasons,id'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'refund_method' => ['nullable', 'string'],
            'restocking_fee' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'exists:order_items,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['nullable', 'in:sellable,damaged,defective'],
            'items.*.restock' => ['nullable', 'boolean'],
            'items.*.reason' => ['nullable', 'string'],
        ];
    }
}
