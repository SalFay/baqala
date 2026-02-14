<?php

namespace App\Http\Requests\Api\V1\Return;

use App\Enums\ReturnType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rules\Enum;
use App\Http\Responses\ApiResponse;

class CreateReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'type' => ['required', new Enum(ReturnType::class)],
            'reason_id' => 'nullable|integer|exists:return_reasons,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|integer|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'nullable|string|in:sellable,damaged,defective',
            'items.*.reason' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'order_id.required' => 'Order is required',
            'order_id.exists' => 'Order not found',
            'type.required' => 'Return type is required',
            'items.required' => 'At least one item is required for return',
            'items.min' => 'At least one item is required for return',
            'items.*.order_item_id.required' => 'Order item is required',
            'items.*.order_item_id.exists' => 'Order item not found',
            'items.*.quantity.required' => 'Return quantity is required',
            'items.*.quantity.min' => 'Return quantity must be at least 1',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
