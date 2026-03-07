<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Product\StoreProductRequest;
use App\Http\Requests\Api\Product\UpdateProductRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Product;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Products/Index', [
            'categories' => CategoryResource::collection(Category::select('id', 'name')->orderBy('name')->get()),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Product::class,
            with: ['category', 'storeInventories'],
            resource: ProductResource::class,
            options: [
                'searchColumns' => ['name', 'sku', 'barcode'],
                'filterColumns' => [
                    'category_id' => 'exact',
                    'is_active' => 'exact',
                    'type' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
                'postProcess' => function ($product) {
                    // Add computed stock_qty for listing
                    $product->stock_qty = $product->storeInventories->sum('quantity');
                    return $product;
                },
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'categories' => CategoryResource::collection(Category::select('id', 'name')->orderBy('name')->get()),
        ]);
    }

    public function edit(Product $product): Response
    {
        $product->load(['category', 'variants', 'storeInventories.store']);

        return Inertia::render('Products/Edit', [
            'product' => new ProductResource($product),
            'categories' => CategoryResource::collection(Category::select('id', 'name')->orderBy('name')->get()),
        ]);
    }

    public function store(StoreProductRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new ProductResource($product),
                'notifications' => [['type' => 'success', 'message' => 'Product created successfully']],
            ], 201);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse|JsonResponse
    {
        $product->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new ProductResource($product->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Product updated successfully']],
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse|JsonResponse
    {
        $product->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'notifications' => [['type' => 'success', 'message' => 'Product deleted successfully']],
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
