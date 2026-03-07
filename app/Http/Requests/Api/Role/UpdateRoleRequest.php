<?php

namespace App\Http\Requests\Api\Role;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role')?->id;

        return [
            'name' => ['sometimes', 'string', 'max:255', 'unique:roles,name,' . $roleId],
            'color' => ['nullable', 'string', 'max:9'],
            'permissions' => ['array'],
            'permissions.*' => ['string'],
        ];
    }
}
