<?php

namespace App\Http\Controllers\Api;

use App\Enums\InventoryMovementType;
use App\Http\Controllers\Controller;
use App\Models\StoreInventory;
use App\Services\Inventory\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $inventories = StoreInventory::query()
            ->with(['product', 'variant', 'store'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->product_id, fn($q, $id) => $q->where('product_id', $id))
            ->when($request->low_stock, function ($q) {
                $q->whereRaw('quantity <= COALESCE(low_stock_threshold, 5)');
            })
            ->when($request->out_of_stock, fn($q) => $q->where('quantity', '<=', 0))
            ->when($request->search, function ($q, $term) {
                $q->whereHas('product', fn($q) => $q->search($term));
            })
            ->orderBy('quantity')
            ->paginate($request->per_page ?? 50);

        return response()->json($inventories);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $storeId = $request->store_id ?? 1;

        $products = $this->inventoryService->getLowStockProducts(
            $storeId,
            $request->threshold
        );

        return response()->json($products);
    }

    public function adjust(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        $movement = $this->inventoryService->adjustStock(
            $validated['store_id'],
            $validated['product_id'],
            $validated['variant_id'] ?? null,
            $validated['quantity'],
            $validated['reason'] ?? null
        );

        return response()->json([
            'message' => 'Stock adjusted successfully',
            'movement' => $movement,
            'new_quantity' => $movement->quantity_after,
        ]);
    }

    public function movements(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'nullable|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'type' => 'nullable|string',
            'limit' => 'nullable|integer|min:1|max:200',
        ]);

        $movements = $this->inventoryService->getMovementHistory(
            $validated['store_id'],
            $validated['product_id'] ?? null,
            $validated['variant_id'] ?? null,
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null,
            $validated['limit'] ?? 50
        );

        return response()->json($movements);
    }

    public function stockCount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.counted_quantity' => 'required|integer|min:0',
        ]);

        $results = [];

        foreach ($validated['items'] as $item) {
            $movement = $this->inventoryService->stockCount(
                $validated['store_id'],
                $item['product_id'],
                $item['variant_id'] ?? null,
                $item['counted_quantity']
            );

            $results[] = [
                'product_id' => $item['product_id'],
                'variant_id' => $item['variant_id'] ?? null,
                'previous_quantity' => $movement->quantity_before,
                'counted_quantity' => $item['counted_quantity'],
                'difference' => $movement->quantity,
            ];
        }

        return response()->json([
            'message' => 'Stock count recorded successfully',
            'results' => $results,
        ]);
    }

    public function setInitialStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:0',
            'unit_cost' => 'nullable|numeric|min:0',
        ]);

        $movement = $this->inventoryService->setInitialStock(
            $validated['store_id'],
            $validated['product_id'],
            $validated['variant_id'] ?? null,
            $validated['quantity'],
            $validated['unit_cost'] ?? null
        );

        return response()->json([
            'message' => 'Initial stock set successfully',
            'movement' => $movement,
        ]);
    }
}
