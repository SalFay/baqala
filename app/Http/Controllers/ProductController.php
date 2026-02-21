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
    /**
     * Display products listing page
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        $categories = Category::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Products/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Get paginated products listing for DataGridTable (server-side row model)
     */
    public function listing(Request $request): JsonResponse
    {
        $query = Product::query()
            ->with(['category', 'storeInventories']);

        // Handle soft deleted toggle
        if ($request->input('soft_deleted')) {
            $query->onlyTrashed();
        }

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        // Handle filterTree from GlobalFilter
        if ($filterTree = $request->input('filterTree')) {
            $this->applyFilterTree($query, $filterTree);
        }

        // Sorting
        $sortModel = $request->input('sort', []);
        if (!empty($sortModel)) {
            foreach ($sortModel as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $perPage = $request->input('pageSize', 20);
        $page = $request->input('current', 1);
        $products = $query->paginate($perPage, ['*'], 'page', $page);

        $data = $products->map(fn($product) => [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'barcode' => $product->barcode,
            'category' => ['name' => $product->category?->name],
            'category_id' => $product->category_id,
            'price' => $product->sale_price,
            'cost' => $product->cost_price,
            'stock_qty' => $product->storeInventories->sum('quantity'),
            'in_stock' => $product->storeInventories->sum('quantity') > 0,
            'is_active' => $product->is_active,
            'image_url' => $product->image_url,
            'deleted_at' => $product->deleted_at,
        ]);

        // Handle export
        if ($request->input('export')) {
            return $this->exportProducts($data->toArray());
        }

        return response()->json([
            'data' => $data,
            'total' => $products->total(),
        ]);
    }

    /**
     * Apply filterTree conditions to query
     */
    private function applyFilterTree($query, array $filterTree): void
    {
        if (empty($filterTree['conditions'])) {
            return;
        }

        foreach ($filterTree['conditions'] as $condition) {
            if (isset($condition['conditions'])) {
                // Nested conditions group
                $this->applyFilterTree($query, $condition);
            } else {
                // Single condition
                $field = $condition['field'] ?? null;
                $operator = $condition['operator'] ?? 'is';
                $value = $condition['value'] ?? [];

                if (!$field || empty($value)) {
                    continue;
                }

                // Map field names for relationships
                $dbField = match($field) {
                    'category' => 'category_id',
                    default => $field,
                };

                // Apply condition based on operator
                switch ($operator) {
                    case 'is':
                    case '=':
                        if (count($value) === 1) {
                            $query->where($dbField, $value[0]);
                        } else {
                            $query->whereIn($dbField, $value);
                        }
                        break;
                    case 'is_not':
                    case '!=':
                        $query->whereNotIn($dbField, $value);
                        break;
                    case 'contains':
                        $query->where($dbField, 'like', "%{$value[0]}%");
                        break;
                    case 'starts_with':
                        $query->where($dbField, 'like', "{$value[0]}%");
                        break;
                    case 'ends_with':
                        $query->where($dbField, 'like', "%{$value[0]}");
                        break;
                    case 'is_empty':
                        $query->whereNull($dbField);
                        break;
                    case 'is_not_empty':
                        $query->whereNotNull($dbField);
                        break;
                    case '>':
                        $query->where($dbField, '>', $value[0]);
                        break;
                    case '<':
                        $query->where($dbField, '<', $value[0]);
                        break;
                    case '>=':
                        $query->where($dbField, '>=', $value[0]);
                        break;
                    case '<=':
                        $query->where($dbField, '<=', $value[0]);
                        break;
                    case 'between':
                        if (count($value) >= 2) {
                            $query->whereBetween($dbField, [$value[0], $value[1]]);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Export products to CSV
     */
    private function exportProducts(array $data): JsonResponse
    {
        $filename = 'products_' . date('Y-m-d_His') . '.csv';
        $headers = ['ID', 'Name', 'SKU', 'Barcode', 'Category', 'Price', 'Cost', 'Stock', 'Status'];

        $callback = function () use ($data, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['id'],
                    $row['name'],
                    $row['sku'] ?? '',
                    $row['barcode'] ?? '',
                    $row['category']['name'] ?? '',
                    $row['price'] ?? 0,
                    $row['cost'] ?? 0,
                    $row['stock_qty'] ?? 0,
                    $row['is_active'] ? 'Active' : 'Inactive',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
