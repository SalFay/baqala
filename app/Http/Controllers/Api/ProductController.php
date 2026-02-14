<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'variants', 'storeInventories'])
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->search, fn($q, $term) => $q->search($term))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 20);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'arabic_name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|unique:products,sku',
            'barcode' => 'nullable|string',
            'type' => 'required|in:simple,variable',
            'category_id' => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'taxable' => 'boolean',
            'status' => 'nullable|string',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'store_id' => 'nullable|exists:stores,id',
            'product_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('product_image')) {
            $validated['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return response()->json($product->load(['category', 'variants']), 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json(
            $product->load(['category', 'variants.attributeValues.attribute', 'storeInventories'])
        );
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'arabic_name' => 'nullable|string|max:255',
            'sku' => 'nullable|string|unique:products,sku,' . $product->id,
            'barcode' => 'nullable|string',
            'type' => 'sometimes|in:simple,variable',
            'category_id' => 'sometimes|exists:categories,id',
            'purchase_price' => 'sometimes|numeric|min:0',
            'sale_price' => 'sometimes|numeric|min:0',
            'taxable' => 'boolean',
            'status' => 'nullable|string',
            'track_inventory' => 'boolean',
            'low_stock_threshold' => 'nullable|integer|min:0',
            'product_image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('product_image')) {
            if ($product->product_image) {
                Storage::disk('public')->delete($product->product_image);
            }
            $validated['product_image'] = $request->file('product_image')->store('products', 'public');
        }

        $product->update($validated);

        return response()->json($product->fresh(['category', 'variants']));
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:1']);

        $products = Product::query()
            ->with(['category', 'variants'])
            ->search($request->q)
            ->where('status', 'active')
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    public function findByBarcode(string $barcode): JsonResponse
    {
        // Check product variants first
        $variant = ProductVariant::with('product.category')
            ->where('barcode', $barcode)
            ->first();

        if ($variant) {
            return response()->json([
                'product' => $variant->product,
                'variant' => $variant,
            ]);
        }

        // Check products
        $product = Product::with(['category', 'variants'])
            ->where('barcode', $barcode)
            ->first();

        if ($product) {
            return response()->json([
                'product' => $product,
                'variant' => null,
            ]);
        }

        return response()->json(['message' => 'Product not found'], 404);
    }

    public function storeVariant(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku',
            'barcode' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'attributes' => 'required|array',
            'attributes.*.attribute_id' => 'required|exists:product_attributes,id',
            'attributes.*.value_id' => 'required|exists:product_attribute_values,id',
        ]);

        $variant = $product->variants()->create([
            'sku' => $validated['sku'],
            'barcode' => $validated['barcode'],
            'name' => $validated['name'],
            'purchase_price' => $validated['purchase_price'],
            'sale_price' => $validated['sale_price'],
            'compare_price' => $validated['compare_price'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['attributes'] as $attr) {
            $variant->attributeValues()->attach($attr['value_id'], [
                'product_attribute_id' => $attr['attribute_id'],
            ]);
        }

        return response()->json($variant->load('attributeValues'), 201);
    }

    public function updateVariant(Request $request, Product $product, ProductVariant $variant): JsonResponse
    {
        $validated = $request->validate([
            'sku' => 'nullable|string|unique:product_variants,sku,' . $variant->id,
            'barcode' => 'nullable|string',
            'name' => 'nullable|string|max:255',
            'purchase_price' => 'sometimes|numeric|min:0',
            'sale_price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $variant->update($validated);

        return response()->json($variant->fresh('attributeValues'));
    }
}
