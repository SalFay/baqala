<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Enums\StockTransferStatus;
use App\Services\Inventory\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockTransferController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * List stock transfers
     */
    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with(['fromStore', 'toStore', 'createdBy']);

        if ($search = $request->input('search')) {
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($fromStoreId = $request->input('from_store_id')) {
            $query->where('from_store_id', $fromStoreId);
        }

        if ($toStoreId = $request->input('to_store_id')) {
            $query->where('to_store_id', $toStoreId);
        }

        $transfers = $query->latest()->paginate($request->input('per_page', 20));

        return $this->paginated($transfers);
    }

    /**
     * Create stock transfer
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id|different:from_store_id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated) {
            // Generate transfer number
            $transferNumber = 'ST-' . date('Ymd') . '-' . str_pad(
                StockTransfer::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],
                'created_by' => auth()->id(),
                'status' => StockTransferStatus::DRAFT,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity_requested' => $item['quantity_requested'],
                    'quantity_sent' => 0,
                    'quantity_received' => 0,
                ]);
            }

            return $this->created(
                $transfer->load(['items.product', 'fromStore', 'toStore']),
                'Stock transfer created successfully'
            );
        });
    }

    /**
     * Show stock transfer
     */
    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        $stockTransfer->load([
            'items.product',
            'items.productVariant',
            'fromStore',
            'toStore',
            'createdBy',
            'approvedBy',
            'receivedBy',
        ]);

        return $this->success($stockTransfer);
    }

    /**
     * Update stock transfer (draft only)
     */
    public function update(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->status !== StockTransferStatus::DRAFT) {
            return $this->error('Can only edit draft transfers', 422);
        }

        $validated = $request->validate([
            'from_store_id' => 'sometimes|required|exists:stores,id',
            'to_store_id' => 'sometimes|required|exists:stores,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity_requested' => 'required|integer|min:1',
        ]);

        // Validate different stores
        $fromStore = $validated['from_store_id'] ?? $stockTransfer->from_store_id;
        $toStore = $validated['to_store_id'] ?? $stockTransfer->to_store_id;

        if ($fromStore === $toStore) {
            return $this->error('Source and destination stores must be different', 422);
        }

        return DB::transaction(function () use ($validated, $stockTransfer) {
            $stockTransfer->update([
                'from_store_id' => $validated['from_store_id'] ?? $stockTransfer->from_store_id,
                'to_store_id' => $validated['to_store_id'] ?? $stockTransfer->to_store_id,
                'notes' => $validated['notes'] ?? $stockTransfer->notes,
            ]);

            if (isset($validated['items'])) {
                $stockTransfer->items()->delete();

                foreach ($validated['items'] as $item) {
                    StockTransferItem::create([
                        'stock_transfer_id' => $stockTransfer->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity_requested' => $item['quantity_requested'],
                        'quantity_sent' => 0,
                        'quantity_received' => 0,
                    ]);
                }
            }

            return $this->success(
                $stockTransfer->load(['items.product', 'fromStore', 'toStore']),
                'Stock transfer updated successfully'
            );
        });
    }

    /**
     * Delete stock transfer (draft only)
     */
    public function destroy(StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->status !== StockTransferStatus::DRAFT) {
            return $this->error('Can only delete draft transfers', 422);
        }

        $stockTransfer->items()->delete();
        $stockTransfer->delete();

        return $this->success(null, 'Stock transfer deleted successfully');
    }

    /**
     * Submit for approval
     */
    public function submit(StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->status !== StockTransferStatus::DRAFT) {
            return $this->error('Can only submit draft transfers', 422);
        }

        $stockTransfer->update(['status' => StockTransferStatus::PENDING]);

        return $this->success($stockTransfer, 'Stock transfer submitted for approval');
    }

    /**
     * Approve and ship transfer
     */
    public function ship(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if (!in_array($stockTransfer->status, [StockTransferStatus::DRAFT, StockTransferStatus::PENDING])) {
            return $this->error('Cannot ship this transfer', 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_transfer_item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.quantity_sent' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $stockTransfer) {
            foreach ($validated['items'] as $item) {
                $transferItem = StockTransferItem::find($item['stock_transfer_item_id']);

                // Validate stock availability
                $available = $this->inventoryService->getAvailableStock(
                    $stockTransfer->from_store_id,
                    $transferItem->product_id,
                    $transferItem->product_variant_id
                );

                if ($available < $item['quantity_sent']) {
                    return $this->error(
                        "Insufficient stock for product ID {$transferItem->product_id}",
                        422
                    );
                }

                $transferItem->update(['quantity_sent' => $item['quantity_sent']]);

                // Deduct from source store
                $this->inventoryService->removeStock(
                    $stockTransfer->from_store_id,
                    $transferItem->product_id,
                    $transferItem->product_variant_id,
                    $item['quantity_sent'],
                    'transfer_out',
                    $stockTransfer,
                    "Transfer to {$stockTransfer->toStore->name}"
                );
            }

            $stockTransfer->update([
                'status' => StockTransferStatus::IN_TRANSIT,
                'approved_by' => auth()->id(),
                'shipped_at' => now(),
            ]);

            return $this->success($stockTransfer->load('items'), 'Stock transfer shipped');
        });
    }

    /**
     * Receive transfer at destination
     */
    public function receive(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->status !== StockTransferStatus::IN_TRANSIT) {
            return $this->error('Can only receive in-transit transfers', 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_transfer_item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        return DB::transaction(function () use ($validated, $stockTransfer) {
            foreach ($validated['items'] as $item) {
                $transferItem = StockTransferItem::find($item['stock_transfer_item_id']);

                $transferItem->update([
                    'quantity_received' => $item['quantity_received'],
                    'notes' => $item['notes'] ?? null,
                ]);

                // Add to destination store
                if ($item['quantity_received'] > 0) {
                    $this->inventoryService->addStock(
                        $stockTransfer->to_store_id,
                        $transferItem->product_id,
                        $transferItem->product_variant_id,
                        $item['quantity_received'],
                        'transfer_in',
                        null,
                        $stockTransfer,
                        "Transfer from {$stockTransfer->fromStore->name}"
                    );
                }
            }

            $stockTransfer->update([
                'status' => StockTransferStatus::RECEIVED,
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            return $this->success($stockTransfer->load('items'), 'Stock transfer received');
        });
    }

    /**
     * Cancel transfer
     */
    public function cancel(StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->status === StockTransferStatus::RECEIVED) {
            return $this->error('Cannot cancel received transfers', 422);
        }

        // If already shipped, need to reverse inventory
        if ($stockTransfer->status === StockTransferStatus::IN_TRANSIT) {
            return DB::transaction(function () use ($stockTransfer) {
                foreach ($stockTransfer->items as $item) {
                    if ($item->quantity_sent > 0) {
                        // Add back to source store
                        $this->inventoryService->addStock(
                            $stockTransfer->from_store_id,
                            $item->product_id,
                            $item->product_variant_id,
                            $item->quantity_sent,
                            'adjustment_add',
                            null,
                            $stockTransfer,
                            "Transfer cancelled - stock returned"
                        );
                    }
                }

                $stockTransfer->update(['status' => StockTransferStatus::CANCELLED]);

                return $this->success($stockTransfer, 'Stock transfer cancelled and inventory restored');
            });
        }

        $stockTransfer->update(['status' => StockTransferStatus::CANCELLED]);

        return $this->success($stockTransfer, 'Stock transfer cancelled');
    }
}
