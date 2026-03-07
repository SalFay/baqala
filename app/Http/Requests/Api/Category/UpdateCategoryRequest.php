<?php

namespace App\Http\Requests\Api\Category;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'parent_id' => ['nullable', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) {
                return;
            }

            $category = $this->route('category');
            $parentId = $this->parent_id;

            if ($parentId && $category) {
                // Cannot set parent to self
                if ($parentId == $category->id) {
                    $validator->errors()->add('parent_id', 'Cannot set parent to self.');
                    return;
                }

                // Cannot set parent to descendant
                $descendantIds = $this->getDescendantIds($category);
                if (in_array($parentId, $descendantIds)) {
                    $validator->errors()->add('parent_id', 'Cannot set parent to a descendant.');
                }
            }
        });
    }

    private function getDescendantIds(Category $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }
}
