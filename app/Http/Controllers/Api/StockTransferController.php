<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StockTransfer\StoreStockTransferRequest;
use App\Http\Requests\Api\StockTransfer\UpdateStockTransferStatusRequest;
use App\Http\Resources\StatusHistoryResource;
use App\Http\Resources\StatusResource;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use App\Enums\StockTransferStatus;
use App\Services\Inventory\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Activity;

class StockTransferController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = StockTransfer::with(['fromStore', 'toStore', 'createdBy', 'currentStatus']);

        if ($search = $request->input('search')) {
            $query->where('transfer_number', 'like', "%{$search}%");
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($statusCode = $request->input('status_code')) {
            $query->whereStatus($statusCode);
        }

        if ($fromStoreId = $request->input('from_store_id')) {
            $query->where('from_store_id', $fromStoreId);
        }

        if ($toStoreId = $request->input('to_store_id')) {
            $query->where('to_store_id', $toStoreId);
        }

        $transfers = $query->latest()->paginate($request->input('per_page', 20));

        return StockTransferResource::collection($transfers)->response();
    }

    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {
            $transferNumber = 'ST-' . date('Ymd') . '-' . str_pad(
                StockTransfer::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $transfer = StockTransfer::create([
                'transfer_number' => $transferNumber,
                'from_store_id' => $validated['from_store_id'],
                'to_store_id' => $validated['to_store_id'],
                'created_by' => auth()->id(),
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'received_quantity' => 0,
                ]);
            }

            return StockTransferResource::make($transfer->load(['items.product', 'fromStore', 'toStore', 'currentStatus']))
                ->response()
                ->setStatusCode(201);
        });
    }

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
            'currentStatus',
            'statusHistories.status',
            'statusHistories.previousStatus',
            'statusHistories.user',
        ]);

        return StockTransferResource::make($stockTransfer)->response();
    }

    public function update(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if (!$stockTransfer->hasStatus('pending')) {
            return response()->json(['message' => 'Can only edit pending transfers'], 422);
        }

        $validated = $request->validate([
            'from_store_id' => 'sometimes|required|exists:stores,id',
            'to_store_id' => 'sometimes|required|exists:stores,id',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $fromStore = $validated['from_store_id'] ?? $stockTransfer->from_store_id;
        $toStore = $validated['to_store_id'] ?? $stockTransfer->to_store_id;

        if ($fromStore === $toStore) {
            return response()->json(['message' => 'Source and destination stores must be different'], 422);
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
                        'quantity' => $item['quantity'],
                        'received_quantity' => 0,
                    ]);
                }
            }

            return StockTransferResource::make($stockTransfer->load(['items.product', 'fromStore', 'toStore']))
                ->response();
        });
    }

    public function destroy(StockTransfer $stockTransfer): JsonResponse
    {
        if (!$stockTransfer->hasStatus('pending')) {
            return response()->json(['message' => 'Can only delete pending transfers'], 422);
        }

        $stockTransfer->items()->delete();
        $stockTransfer->delete();

        return response()->json(['message' => 'Stock transfer deleted successfully']);
    }

    public function updateStatus(UpdateStockTransferStatusRequest $request, StockTransfer $stockTransfer): JsonResponse
    {
        try {
            $stockTransfer->changeStatus(
                $request->validated('status'),
                $request->validated('reason')
            );

            return response()->json([
                'message' => 'Status updated successfully',
                'stock_transfer' => StockTransferResource::make($stockTransfer->fresh(['currentStatus', 'statusHistories.status'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function statusHistory(StockTransfer $stockTransfer): JsonResponse
    {
        $histories = $stockTransfer->statusHistories()
            ->with(['status', 'previousStatus', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return StatusHistoryResource::collection($histories)->response();
    }

    public function availableStatuses(StockTransfer $stockTransfer): JsonResponse
    {
        return response()->json([
            'current_status' => StatusResource::make($stockTransfer->currentStatus),
            'available_statuses' => StatusResource::collection($stockTransfer->getAllowedNextStatuses()),
        ]);
    }

    public function ship(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if (!$stockTransfer->hasAnyStatus(['pending', 'approved'])) {
            return response()->json(['message' => 'Cannot ship this transfer'], 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.stock_transfer_item_id' => 'required|exists:stock_transfer_items,id',
            'items.*.quantity_sent' => 'required|integer|min:1',
        ]);

        return DB::transaction(function () use ($validated, $stockTransfer) {
            foreach ($validated['items'] as $item) {
                $transferItem = StockTransferItem::find($item['stock_transfer_item_id']);

                $available = $this->inventoryService->getAvailableStock(
                    $stockTransfer->from_store_id,
                    $transferItem->product_id,
                    $transferItem->product_variant_id
                );

                if ($available < $item['quantity_sent']) {
                    return response()->json([
                        'message' => "Insufficient stock for product ID {$transferItem->product_id}"
                    ], 422);
                }

                $transferItem->update(['quantity' => $item['quantity_sent']]);

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

            $stockTransfer->changeStatus('in_transit', 'Shipped');
            $stockTransfer->update([
                'approved_by' => auth()->id(),
                'shipped_at' => now(),
            ]);

            return StockTransferResource::make($stockTransfer->load('items'))->response();
        });
    }

    public function receive(Request $request, StockTransfer $stockTransfer): JsonResponse
    {
        if (!$stockTransfer->hasStatus('in_transit')) {
            return response()->json(['message' => 'Can only receive in-transit transfers'], 422);
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
                    'received_quantity' => $item['quantity_received'],
                ]);

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

            $stockTransfer->changeStatus('completed', 'Received');
            $stockTransfer->update([
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);

            return StockTransferResource::make($stockTransfer->load('items'))->response();
        });
    }

    public function cancel(StockTransfer $stockTransfer): JsonResponse
    {
        if ($stockTransfer->hasStatus('completed')) {
            return response()->json(['message' => 'Cannot cancel completed transfers'], 422);
        }

        if ($stockTransfer->hasStatus('in_transit')) {
            return DB::transaction(function () use ($stockTransfer) {
                foreach ($stockTransfer->items as $item) {
                    if ($item->quantity > 0) {
                        $this->inventoryService->addStock(
                            $stockTransfer->from_store_id,
                            $item->product_id,
                            $item->product_variant_id,
                            $item->quantity,
                            'adjustment_add',
                            null,
                            $stockTransfer,
                            "Transfer cancelled - stock returned"
                        );
                    }
                }

                $stockTransfer->changeStatus('cancelled', 'Transfer cancelled - inventory restored');

                return response()->json([
                    'message' => 'Stock transfer cancelled and inventory restored',
                    'stock_transfer' => StockTransferResource::make($stockTransfer),
                ]);
            });
        }

        $stockTransfer->changeStatus('cancelled', 'Transfer cancelled');

        return response()->json([
            'message' => 'Stock transfer cancelled',
            'stock_transfer' => StockTransferResource::make($stockTransfer),
        ]);
    }
}
