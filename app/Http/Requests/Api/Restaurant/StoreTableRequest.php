<?php

namespace App\Http\Requests\Api\Restaurant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'section' => ['nullable', 'string', 'max:100'],
            'floor' => ['nullable', 'string', 'max:100'],
            'shape' => ['nullable', 'string', 'in:square,rectangle,circle'],
            'position_x' => ['nullable', 'integer'],
            'position_y' => ['nullable', 'integer'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'is_active' => ['boolean'],
        ];
    }
}
