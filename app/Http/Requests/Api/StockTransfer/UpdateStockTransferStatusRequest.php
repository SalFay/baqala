<?php

namespace App\Http\Requests\Api\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStockTransferStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Status is required.',
        ];
    }
}
