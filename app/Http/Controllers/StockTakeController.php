<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTake;
use App\Models\StockTakeItem;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Services\Inventory\InventoryService;
use App\Enums\InventoryMovementType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTakeController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Server-side listing for DataGridTable
     */
    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        $query = StockTake::with(['creator', 'category'])
            ->forStore($store->id);

        // Search
        if ($request->search) {
            $query->where('stock_take_number', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $stockTakes = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($st) => [
                'id' => $st->id,
                'stock_take_number' => $st->stock_take_number,
                'type' => $st->type,
                'category' => $st->category?->name,
                'status' => $st->status,
                'items_count' => $st->items_count ?? 0,
                'variance_count' => $st->variance_count ?? 0,
                'created_by' => $st->creator?->name,
                'created_at' => $st->created_at,
                'completed_at' => $st->completed_at,
            ]);

        return response()->json([
            'data' => $stockTakes,
            'total' => $total,
        ]);
    }

    /**
     * List stock takes with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $store = Store::first();

        $query = StockTake::with(['creator', 'completedByUser', 'category'])
            ->forStore($store->id)
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->status($request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $perPage = $request->input('per_page', 20);
        $stockTakes = $query->paginate($perPage);

        return response()->json([
            'data' => collect($stockTakes->items())->map->toApiArray(),
            'meta' => [
                'current_page' => $stockTakes->currentPage(),
                'last_page' => $stockTakes->lastPage(),
                'per_page' => $stockTakes->perPage(),
                'total' => $stockTakes->total(),
            ],
        ]);
    }

    /**
     * Get a single stock take with items.
     */
    public function show(StockTake $stockTake): JsonResponse
    {
        $stockTake->load(['creator', 'completedByUser', 'category', 'items.product', 'items.variant']);

        return response()->json([
            'data' => [
                ...$stockTake->toApiArray(),
                'items' => $stockTake->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'variant_id' => $item->product_variant_id,
                    'product_name' => $item->product_name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'expected_quantity' => $item->expected_quantity,
                    'counted_quantity' => $item->counted_quantity,
                    'variance' => $item->variance,
                    'variance_status' => $item->variance_status,
                    'variance_color' => $item->variance_color,
                    'location' => $item->location,
                    'notes' => $item->notes,
                    'counted_at' => $item->counted_at?->format('Y-m-d H:i'),
                    'counted_by' => $item->countedByUser?->name,
                ]),
            ],
        ]);
    }

    /**
     * Create a new stock take.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|string|in:full,partial,category,location',
            'category_id' => 'nullable|exists:categories,id',
            'location' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:1000',
            'product_ids' => 'nullable|array',
            'product_ids.*' => 'exists:products,id',
        ]);

        $store = Store::first();

        $stockTake = DB::transaction(function () use ($validated, $store, $request) {
            $stockTake = StockTake::create([
                'store_id' => $store->id,
                'created_by' => auth()->id(),
                'type' => $validated['type'],
                'category_id' => $validated['category_id'] ?? null,
                'location' => $validated['location'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'status' => StockTake::STATUS_DRAFT,
            ]);

            // Get products based on type
            $productsQuery = Product::with(['variants']);

            if ($validated['type'] === 'full') {
                // All products
            } elseif ($validated['type'] === 'category' && !empty($validated['category_id'])) {
                $productsQuery->where('category_id', $validated['category_id']);
            } elseif ($validated['type'] === 'partial' && !empty($validated['product_ids'])) {
                $productsQuery->whereIn('id', $validated['product_ids']);
            }

            $products = $productsQuery->get();

            // Create stock take items
            foreach ($products as $product) {
                if ($product->has_variants && $product->variants->isNotEmpty()) {
                    foreach ($product->variants as $variant) {
                        $inventory = StoreInventory::where('store_id', $store->id)
                            ->where('product_id', $product->id)
                            ->where('product_variant_id', $variant->id)
                            ->first();

                        StockTakeItem::create([
                            'stock_take_id' => $stockTake->id,
                            'product_id' => $product->id,
                            'product_variant_id' => $variant->id,
                            'sku' => $variant->sku ?? $product->sku,
                            'barcode' => $variant->barcode ?? $product->barcode,
                            'expected_quantity' => $inventory?->quantity ?? 0,
                            'location' => $validated['location'] ?? null,
                        ]);
                    }
                } else {
                    $inventory = StoreInventory::where('store_id', $store->id)
                        ->where('product_id', $product->id)
                        ->whereNull('product_variant_id')
                        ->first();

                    StockTakeItem::create([
                        'stock_take_id' => $stockTake->id,
                        'product_id' => $product->id,
                        'sku' => $product->sku,
                        'barcode' => $product->barcode,
                        'expected_quantity' => $inventory?->quantity ?? 0,
                        'location' => $validated['location'] ?? null,
                    ]);
                }
            }

            return $stockTake;
        });

        return response()->json([
            'message' => 'Stock take created successfully',
            'data' => $stockTake->fresh(['items'])->toApiArray(),
        ], 201);
    }

    /**
     * Start a stock take.
     */
    public function start(StockTake $stockTake): JsonResponse
    {
        if (!$stockTake->canBeStarted()) {
            return response()->json([
                'error' => 'Stock take cannot be started in its current status',
            ], 422);
        }

        $stockTake->start();

        return response()->json([
            'message' => 'Stock take started',
            'data' => $stockTake->fresh()->toApiArray(),
        ]);
    }

    /**
     * Record count for a single item.
     */
    public function countItem(Request $request, StockTake $stockTake, StockTakeItem $item): JsonResponse
    {
        if (!$stockTake->canBeCounted()) {
            return response()->json([
                'error' => 'Stock take is not in progress',
            ], 422);
        }

        $validated = $request->validate([
            'counted_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $item->recordCount($validated['counted_quantity'], auth()->id());

        if (!empty($validated['notes'])) {
            $item->update(['notes' => $validated['notes']]);
        }

        return response()->json([
            'message' => 'Count recorded',
            'data' => [
                'id' => $item->id,
                'counted_quantity' => $item->counted_quantity,
                'variance' => $item->variance,
                'variance_status' => $item->variance_status,
            ],
        ]);
    }

    /**
     * Bulk count items by barcode scan.
     */
    public function scanBarcode(Request $request, StockTake $stockTake): JsonResponse
    {
        if (!$stockTake->canBeCounted()) {
            return response()->json([
                'error' => 'Stock take is not in progress',
            ], 422);
        }

        $validated = $request->validate([
            'barcode' => 'required|string',
            'quantity' => 'nullable|integer|min:1',
        ]);

        $item = $stockTake->items()
            ->where('barcode', $validated['barcode'])
            ->orWhere('sku', $validated['barcode'])
            ->first();

        if (!$item) {
            return response()->json([
                'error' => 'Product not found in this stock take',
            ], 404);
        }

        $quantity = $validated['quantity'] ?? 1;
        $newCount = ($item->counted_quantity ?? 0) + $quantity;

        $item->recordCount($newCount, auth()->id());

        return response()->json([
            'message' => 'Item scanned',
            'data' => [
                'id' => $item->id,
                'product_name' => $item->product_name,
                'counted_quantity' => $item->counted_quantity,
                'expected_quantity' => $item->expected_quantity,
                'variance' => $item->variance,
            ],
        ]);
    }

    /**
     * Complete stock take and apply adjustments.
     */
    public function complete(Request $request, StockTake $stockTake): JsonResponse
    {
        if (!$stockTake->canBeCompleted()) {
            return response()->json([
                'error' => 'Stock take cannot be completed',
            ], 422);
        }

        $validated = $request->validate([
            'apply_adjustments' => 'required|boolean',
        ]);

        DB::transaction(function () use ($stockTake, $validated) {
            $stockTake->complete(auth()->id());

            if ($validated['apply_adjustments']) {
                foreach ($stockTake->items as $item) {
                    if ($item->variance !== 0 && $item->variance !== null) {
                        $this->inventoryService->recordMovement(
                            storeId: $stockTake->store_id,
                            productId: $item->product_id,
                            variantId: $item->product_variant_id,
                            type: InventoryMovementType::STOCK_COUNT,
                            quantity: $item->variance,
                            referenceType: StockTake::class,
                            referenceId: $stockTake->id,
                            notes: "Stock take adjustment: {$stockTake->stock_take_number}"
                        );
                    }
                }
            }
        });

        return response()->json([
            'message' => 'Stock take completed',
            'data' => $stockTake->fresh()->toApiArray(),
        ]);
    }

    /**
     * Cancel stock take.
     */
    public function cancel(StockTake $stockTake): JsonResponse
    {
        if (!$stockTake->canBeCancelled()) {
            return response()->json([
                'error' => 'Stock take cannot be cancelled',
            ], 422);
        }

        $stockTake->cancel();

        return response()->json([
            'message' => 'Stock take cancelled',
            'data' => $stockTake->fresh()->toApiArray(),
        ]);
    }

    /**
     * Delete draft stock take.
     */
    public function destroy(StockTake $stockTake): JsonResponse
    {
        if (!$stockTake->isDraft()) {
            return response()->json([
                'error' => 'Only draft stock takes can be deleted',
            ], 422);
        }

        $stockTake->delete();

        return response()->json([
            'message' => 'Stock take deleted',
        ]);
    }

    /**
     * Get stock take summary stats.
     */
    public function summary(Request $request): JsonResponse
    {
        $store = Store::first();

        $inProgress = StockTake::forStore($store->id)->inProgress()->count();
        $completedThisMonth = StockTake::forStore($store->id)
            ->completed()
            ->whereMonth('completed_at', now()->month)
            ->count();
        $totalAdjustments = StockTakeItem::whereHas('stockTake', function ($q) use ($store) {
            $q->forStore($store->id)->completed()->whereMonth('completed_at', now()->month);
        })->sum('variance');

        return response()->json([
            'data' => [
                'in_progress' => $inProgress,
                'completed_this_month' => $completedThisMonth,
                'total_adjustments_this_month' => (int) $totalAdjustments,
            ],
        ]);
    }
}
