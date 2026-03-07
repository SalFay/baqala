<?php

namespace App\Http\Requests\Api\Expense;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,card,bank_transfer,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
            'status' => ['nullable', 'string', 'in:draft,pending'],
        ];
    }
}
