<?php

namespace App\Http\Requests\Api\V1\Return;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Responses\ApiResponse;

class ProcessReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => 'required|string|in:approve,reject,process,complete',
            'refund_method' => 'nullable|string|in:cash,card,credit,original',
            'refund_amount' => 'nullable|numeric|min:0',
            'restocking_fee' => 'nullable|numeric|min:0',
            'restock_items' => 'nullable|boolean',
            'notes' => 'nullable|string|max:1000',
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|integer|exists:order_return_items,id',
            'items.*.condition' => 'nullable|string|in:sellable,damaged,defective',
            'items.*.restock' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'action.required' => 'Action is required',
            'action.in' => 'Invalid action',
            'refund_method.in' => 'Invalid refund method',
            'refund_amount.min' => 'Refund amount cannot be negative',
            'restocking_fee.min' => 'Restocking fee cannot be negative',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
