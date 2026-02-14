<?php

namespace App\Http\Requests\Api\V1\Cart;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Responses\ApiResponse;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_type' => 'required|string|in:cash,card,credit,mixed',
            'payment_reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
            'paid_amount' => 'nullable|numeric|min:0',
            'payments' => 'nullable|array',
            'payments.*.type' => 'required_with:payments|string|in:cash,card,credit',
            'payments.*.amount' => 'required_with:payments|numeric|min:0.01',
            'payments.*.reference' => 'nullable|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_type.required' => 'Payment type is required',
            'payment_type.in' => 'Invalid payment type',
            'paid_amount.min' => 'Paid amount cannot be negative',
            'payments.*.type.in' => 'Invalid payment type in split payment',
            'payments.*.amount.min' => 'Payment amount must be greater than 0',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            ApiResponse::validationError($validator->errors()->toArray())
        );
    }
}
