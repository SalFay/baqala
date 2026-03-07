<?php

namespace App\Http\Requests\Api\Location;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locationId = $this->route('location')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => ['sometimes', 'string', 'max:50', 'unique:locations,code,' . $locationId],
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
            'selling_price_group_id' => ['nullable', 'exists:selling_price_groups,id'],
            'invoice_prefix' => ['nullable', 'string', 'max:10'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
