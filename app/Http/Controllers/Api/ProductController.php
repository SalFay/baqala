<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
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
            ->when($request->is_active !== null, fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderBy($request->sort_by ?? 'name', $request->sort_order ?? 'asc')
            ->paginate($request->per_page ?? 20);

        return ProductResource::collection($products)
            ->response();
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product = Product::create($validated);

        return ProductResource::make($product->load(['category', 'variants']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): JsonResponse
    {
        return ProductResource::make(
            $product->load(['category', 'vendor', 'variants.attributeValues.attribute', 'storeInventories'])
        )->response();
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $product->update($validated);

        return ProductResource::make($product->fresh(['category', 'variants']))
            ->response();
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
            ->active()
            ->limit(20)
            ->get();

        return ProductResource::collection($products)
            ->response();
    }

    public function findByBarcode(string $barcode): JsonResponse
    {
        // Check product variants first
        $variant = ProductVariant::with('product.category')
            ->where('barcode', $barcode)
            ->first();

        if ($variant) {
            return response()->json([
                'product' => ProductResource::make($variant->product),
                'variant' => $variant,
            ]);
        }

        // Check products
        $product = Product::with(['category', 'variants'])
            ->where('barcode', $barcode)
            ->first();

        if ($product) {
            return response()->json([
                'product' => ProductResource::make($product),
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
            'cost_price' => 'required|numeric|min:0',
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
            'cost_price' => $validated['cost_price'],
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
            'cost_price' => 'sometimes|numeric|min:0',
            'sale_price' => 'sometimes|numeric|min:0',
            'compare_price' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $variant->update($validated);

        return response()->json($variant->fresh('attributeValues'));
    }
}
