<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductAttributeController extends Controller
{
    use ApiResponse;

    /**
     * List all product attributes
     */
    public function index(Request $request): JsonResponse
    {
        $query = ProductAttribute::with('values');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_variation')) {
            $query->where('is_variation', filter_var($request->input('is_variation'), FILTER_VALIDATE_BOOLEAN));
        }

        $attributes = $query->orderBy('sort_order')->get();

        return $this->success($attributes);
    }

    /**
     * Create product attribute
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug',
            'type' => 'required|in:select,color,text,size',
            'is_visible' => 'nullable|boolean',
            'is_variation' => 'nullable|boolean',
            'values' => 'nullable|array',
            'values.*.value' => 'required|string|max:255',
            'values.*.value_ar' => 'nullable|string|max:255',
            'values.*.color_code' => 'nullable|string|max:20',
        ]);

        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['is_visible'] = $validated['is_visible'] ?? true;
        $validated['is_variation'] = $validated['is_variation'] ?? true;
        $validated['sort_order'] = ProductAttribute::max('sort_order') + 1;

        $attribute = ProductAttribute::create($validated);

        // Create values if provided
        if (!empty($validated['values'])) {
            foreach ($validated['values'] as $index => $value) {
                ProductAttributeValue::create([
                    'product_attribute_id' => $attribute->id,
                    'value' => $value['value'],
                    'value_ar' => $value['value_ar'] ?? null,
                    'slug' => Str::slug($value['value']),
                    'color_code' => $value['color_code'] ?? null,
                    'sort_order' => $index + 1,
                ]);
            }
        }

        return $this->created($attribute->load('values'), 'Product attribute created successfully');
    }

    /**
     * Show product attribute
     */
    public function show(ProductAttribute $attribute): JsonResponse
    {
        $attribute->load('values');

        return $this->success($attribute);
    }

    /**
     * Update product attribute
     */
    public function update(Request $request, ProductAttribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'slug' => 'nullable|string|max:255|unique:product_attributes,slug,' . $attribute->id,
            'type' => 'sometimes|required|in:select,color,text,size',
            'is_visible' => 'nullable|boolean',
            'is_variation' => 'nullable|boolean',
        ]);

        if (isset($validated['name']) && !isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $attribute->update($validated);

        return $this->success($attribute->load('values'), 'Product attribute updated successfully');
    }

    /**
     * Delete product attribute
     */
    public function destroy(ProductAttribute $attribute): JsonResponse
    {
        // Check if attribute is used in variants
        if ($attribute->variantAttributes()->exists()) {
            return $this->error('Cannot delete attribute used in product variants', 422);
        }

        $attribute->values()->delete();
        $attribute->delete();

        return $this->success(null, 'Product attribute deleted successfully');
    }

    /**
     * Add value to attribute
     */
    public function storeValue(Request $request, ProductAttribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255',
            'value_ar' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
        ]);

        $value = ProductAttributeValue::create([
            'product_attribute_id' => $attribute->id,
            'value' => $validated['value'],
            'value_ar' => $validated['value_ar'] ?? null,
            'slug' => Str::slug($validated['value']),
            'color_code' => $validated['color_code'] ?? null,
            'sort_order' => $attribute->values()->max('sort_order') + 1,
        ]);

        return $this->created($value, 'Attribute value created successfully');
    }

    /**
     * Update attribute value
     */
    public function updateValue(Request $request, ProductAttributeValue $value): JsonResponse
    {
        $validated = $request->validate([
            'value' => 'sometimes|required|string|max:255',
            'value_ar' => 'nullable|string|max:255',
            'color_code' => 'nullable|string|max:20',
        ]);

        if (isset($validated['value'])) {
            $validated['slug'] = Str::slug($validated['value']);
        }

        $value->update($validated);

        return $this->success($value, 'Attribute value updated successfully');
    }

    /**
     * Delete attribute value
     */
    public function destroyValue(ProductAttributeValue $value): JsonResponse
    {
        // Check if value is used in variants
        if ($value->variantAttributes()->exists()) {
            return $this->error('Cannot delete value used in product variants', 422);
        }

        $value->delete();

        return $this->success(null, 'Attribute value deleted successfully');
    }

    /**
     * Reorder attributes
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:product_attributes,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            ProductAttribute::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return $this->success(null, 'Attributes reordered successfully');
    }

    /**
     * Reorder attribute values
     */
    public function reorderValues(Request $request, ProductAttribute $attribute): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:product_attribute_values,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            ProductAttributeValue::where('id', $id)
                ->where('product_attribute_id', $attribute->id)
                ->update(['sort_order' => $index + 1]);
        }

        return $this->success(null, 'Values reordered successfully');
    }
}
