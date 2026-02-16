<?php

namespace App\Http\Controllers;
use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'storeInventories'])
            ->when($request->category_id, fn($q, $id) => $q->where('category_id', $id))
            ->when($request->search, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%")
                        ->orWhere('barcode', 'like', "%{$term}%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('is_active', $status === 'active'))
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 20);

        $productsData = $products->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => $product->category?->name,
            'category_id' => $product->category_id,
            'price' => $product->sale_price,
            'cost' => $product->cost_price,
            'stock' => $product->storeInventories->sum('quantity'),
            'status' => $product->is_active ? 'Active' : 'Inactive',
            'is_active' => $product->is_active,
            'image_url' => $product->image_url,
        ]);

        // Return JSON for API requests (POS app)
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $productsData,
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ]);
        }

        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'products' => [
                'data' => $productsData,
                'meta' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ],
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Products/Create', [
            'categories' => $categories,
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['category', 'variants', 'storeInventories.store']);

        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Products/Edit', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'sku' => $product->sku,
                'barcode' => $product->barcode,
                'category_id' => $product->category_id,
                'type' => $product->type?->value ?? 'simple',
                'sale_price' => $product->sale_price,
                'cost_price' => $product->cost_price,
                'is_active' => $product->is_active,
                'image_url' => $product->image_url,
                'variants' => $product->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'sku' => $v->sku,
                    'price' => $v->sale_price,
                    'cost' => $v->cost_price,
                ]),
                'inventories' => $product->storeInventories->map(fn($inv) => [
                    'store_id' => $inv->store_id,
                    'store_name' => $inv->store?->name,
                    'quantity' => $inv->quantity,
                    'min_quantity' => $inv->min_quantity,
                    'max_quantity' => $inv->max_quantity,
                ]),
            ],
            'categories' => $categories,
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::create($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $product,
                'message' => 'Product created successfully.',
            ], 201);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->update($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $product->fresh(),
                'message' => 'Product updated successfully.',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        $product->delete();

        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Product deleted successfully.',
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
