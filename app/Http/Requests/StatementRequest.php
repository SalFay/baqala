<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StatementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_date' => ['nullable', 'date', 'before_or_equal:to_date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
        ];
    }

    public function messages(): array
    {
        return [
            'from_date.before_or_equal' => 'From date must be before or equal to To date',
            'to_date.after_or_equal' => 'To date must be after or equal to From date',
        ];
    }
}
