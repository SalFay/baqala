<?php

namespace App\Http\Requests\Api\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        $expense = $this->route('expense');
        return $expense && $expense->canBeEdited();
    }

    public function rules(): array
    {
        return [
            'expense_category_id' => ['sometimes', 'exists:expense_categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'expense_date' => ['sometimes', 'date'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'string', 'in:cash,card,bank_transfer,cheque'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_recurring' => ['boolean'],
            'recurring_frequency' => ['nullable', 'string', 'in:daily,weekly,monthly,yearly'],
        ];
    }

    public function messages(): array
    {
        return [
            'expense_category_id.exists' => 'The selected category does not exist.',
        ];
    }
}
