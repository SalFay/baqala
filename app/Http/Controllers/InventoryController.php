<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StoreInventory;
use App\Models\InventoryMovement;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;

        $inventory = StoreInventory::query()
            ->with(['product.category', 'store'])
            ->where('store_id', $storeId)
            ->when($request->search, function ($q, $term) {
                $q->whereHas('product', function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->when($request->category_id, function ($q, $categoryId) {
                $q->whereHas('product', fn($q) => $q->where('category_id', $categoryId));
            })
            ->when($request->low_stock, function ($q) {
                $q->whereRaw('quantity <= min_quantity');
            })
            ->orderBy($request->sort_by ?? 'updated_at', $request->sort_dir ?? 'desc')
            ->paginate($request->per_page ?? 20);

        return Inertia::render('Inventory/Index', [
            'inventory' => [
                'data' => $inventory->map(fn($inv) => [
                    'id' => $inv->id,
                    'product' => [
                        'id' => $inv->product->id,
                        'name' => $inv->product->name,
                        'sku' => $inv->product->sku,
                        'category' => $inv->product->category?->name,
                    ],
                    'store' => $inv->store?->name,
                    'quantity' => $inv->quantity,
                    'min_quantity' => $inv->min_quantity,
                    'max_quantity' => $inv->max_quantity,
                    'is_low_stock' => $inv->quantity <= $inv->min_quantity,
                    'updated_at' => $inv->updated_at,
                ]),
                'meta' => [
                    'total' => $inventory->total(),
                    'per_page' => $inventory->perPage(),
                    'current_page' => $inventory->currentPage(),
                ],
            ],
            'filters' => $request->only(['search', 'category_id', 'low_stock']),
        ]);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer',
            'type' => 'required|in:add,subtract,set',
            'reason' => 'nullable|string|max:255',
        ]);

        $this->inventoryService->adjustStock(
            $validated['product_id'],
            $validated['store_id'],
            $validated['quantity'],
            $validated['type'],
            $validated['reason'] ?? 'Manual adjustment'
        );

        return response()->json(['message' => 'Inventory adjusted successfully']);
    }

    public function movements(Request $request): Response
    {
        $movements = InventoryMovement::query()
            ->with(['product', 'store', 'user'])
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 50);

        return Inertia::render('Inventory/Movements', [
            'movements' => [
                'data' => $movements->map(fn($m) => [
                    'id' => $m->id,
                    'product' => $m->product?->name,
                    'store' => $m->store?->name,
                    'type' => $m->type,
                    'quantity' => $m->quantity,
                    'before' => $m->quantity_before,
                    'after' => $m->quantity_after,
                    'reason' => $m->reason,
                    'user' => $m->user?->first_name,
                    'created_at' => $m->created_at,
                ]),
                'meta' => [
                    'total' => $movements->total(),
                    'per_page' => $movements->perPage(),
                    'current_page' => $movements->currentPage(),
                ],
            ],
        ]);
    }

    public function lowStock(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;

        $lowStock = $this->inventoryService->getLowStockProducts($storeId);

        return Inertia::render('Inventory/LowStock', [
            'products' => $lowStock,
        ]);
    }
}
