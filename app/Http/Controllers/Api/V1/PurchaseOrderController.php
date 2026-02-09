<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseOrderReceipt;
use App\Models\PurchaseOrderReceiptItem;
use App\Enums\PurchaseOrderStatus;
use App\Services\Inventory\InventoryService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrderController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * List purchase orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::with(['vendor', 'store', 'createdBy']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('vendor', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($vendorId = $request->input('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        if ($storeId = $request->input('store_id')) {
            $query->where('store_id', $storeId);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('order_date', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('order_date', '<=', $toDate);
        }

        $orders = $query->latest()->paginate($request->input('per_page', 20));

        return $this->paginated($orders);
    }

    /**
     * Create purchase order
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'store_id' => 'required|exists:stores,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated) {
            // Generate PO number
            $poNumber = 'PO-' . date('Ymd') . '-' . str_pad(
                PurchaseOrder::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $subtotal = 0;
            $taxAmount = 0;

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $lineTax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                $subtotal += $lineTotal;
                $taxAmount += $lineTax;
            }

            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'vendor_id' => $validated['vendor_id'],
                'store_id' => $validated['store_id'],
                'created_by' => auth()->id(),
                'status' => PurchaseOrderStatus::DRAFT,
                'order_date' => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total' => $subtotal + $taxAmount,
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $lineTotal = $item['quantity_ordered'] * $item['unit_cost'];
                $lineTax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);

                PurchaseOrderItem::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity_ordered' => $item['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_cost' => $item['unit_cost'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'tax_amount' => $lineTax,
                    'line_total' => $lineTotal + $lineTax,
                ]);
            }

            return $this->created(
                $purchaseOrder->load(['items.product', 'items.productVariant', 'vendor', 'store']),
                'Purchase order created successfully'
            );
        });
    }

    /**
     * Show purchase order
     */
    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load([
            'items.product',
            'items.productVariant',
            'vendor',
            'store',
            'createdBy',
            'approvedBy',
            'receipts.items',
        ]);

        return $this->success($purchaseOrder);
    }

    /**
     * Update purchase order (draft only)
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::DRAFT) {
            return $this->error('Can only edit draft purchase orders', 422);
        }

        $validated = $request->validate([
            'vendor_id' => 'sometimes|required|exists:vendors,id',
            'order_date' => 'sometimes|required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'notes' => 'nullable|string|max:1000',
            'items' => 'sometimes|required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_order_items,id',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.product_variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        return DB::transaction(function () use ($validated, $purchaseOrder) {
            $purchaseOrder->update([
                'vendor_id' => $validated['vendor_id'] ?? $purchaseOrder->vendor_id,
                'order_date' => $validated['order_date'] ?? $purchaseOrder->order_date,
                'expected_date' => $validated['expected_date'] ?? $purchaseOrder->expected_date,
                'notes' => $validated['notes'] ?? $purchaseOrder->notes,
            ]);

            if (isset($validated['items'])) {
                // Delete old items and recreate
                $purchaseOrder->items()->delete();

                $subtotal = 0;
                $taxAmount = 0;

                foreach ($validated['items'] as $item) {
                    $lineTotal = $item['quantity_ordered'] * $item['unit_cost'];
                    $lineTax = $lineTotal * (($item['tax_rate'] ?? 0) / 100);
                    $subtotal += $lineTotal;
                    $taxAmount += $lineTax;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $purchaseOrder->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'] ?? null,
                        'quantity_ordered' => $item['quantity_ordered'],
                        'quantity_received' => 0,
                        'unit_cost' => $item['unit_cost'],
                        'tax_rate' => $item['tax_rate'] ?? 0,
                        'tax_amount' => $lineTax,
                        'line_total' => $lineTotal + $lineTax,
                    ]);
                }

                $purchaseOrder->update([
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $subtotal + $taxAmount,
                ]);
            }

            return $this->success(
                $purchaseOrder->load(['items.product', 'vendor']),
                'Purchase order updated successfully'
            );
        });
    }

    /**
     * Delete purchase order (draft only)
     */
    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::DRAFT) {
            return $this->error('Can only delete draft purchase orders', 422);
        }

        $purchaseOrder->items()->delete();
        $purchaseOrder->delete();

        return $this->success(null, 'Purchase order deleted successfully');
    }

    /**
     * Submit for approval
     */
    public function submit(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::DRAFT) {
            return $this->error('Can only submit draft purchase orders', 422);
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::PENDING_APPROVAL]);

        return $this->success($purchaseOrder, 'Purchase order submitted for approval');
    }

    /**
     * Approve purchase order
     */
    public function approve(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== PurchaseOrderStatus::PENDING_APPROVAL) {
            return $this->error('Can only approve pending purchase orders', 422);
        }

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::APPROVED,
            'approved_by' => auth()->id(),
        ]);

        return $this->success($purchaseOrder, 'Purchase order approved');
    }

    /**
     * Mark as ordered
     */
    public function order(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, [PurchaseOrderStatus::APPROVED, PurchaseOrderStatus::PENDING_APPROVAL])) {
            return $this->error('Invalid status for ordering', 422);
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::ORDERED]);

        return $this->success($purchaseOrder, 'Purchase order marked as ordered');
    }

    /**
     * Receive items
     */
    public function receive(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (!in_array($purchaseOrder->status, [
            PurchaseOrderStatus::ORDERED,
            PurchaseOrderStatus::PARTIAL,
        ])) {
            return $this->error('Cannot receive items for this order', 422);
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.purchase_order_item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:1',
            'items.*.condition' => 'nullable|in:good,damaged',
            'items.*.notes' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        return DB::transaction(function () use ($validated, $purchaseOrder) {
            // Create receipt
            $receipt = PurchaseOrderReceipt::create([
                'purchase_order_id' => $purchaseOrder->id,
                'received_by' => auth()->id(),
                'receipt_date' => now(),
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $poItem = PurchaseOrderItem::find($item['purchase_order_item_id']);

                // Create receipt item
                PurchaseOrderReceiptItem::create([
                    'purchase_order_receipt_id' => $receipt->id,
                    'purchase_order_item_id' => $item['purchase_order_item_id'],
                    'quantity_received' => $item['quantity_received'],
                    'condition' => $item['condition'] ?? 'good',
                    'notes' => $item['notes'] ?? null,
                ]);

                // Update PO item received quantity
                $poItem->increment('quantity_received', $item['quantity_received']);

                // Add to inventory (only good condition items)
                if (($item['condition'] ?? 'good') === 'good') {
                    $this->inventoryService->addStock(
                        $purchaseOrder->store_id,
                        $poItem->product_id,
                        $poItem->product_variant_id,
                        $item['quantity_received'],
                        'purchase',
                        $poItem->unit_cost,
                        $purchaseOrder,
                        "Received from PO {$purchaseOrder->po_number}"
                    );
                }
            }

            // Update PO status
            $allReceived = $purchaseOrder->items->every(fn($item) =>
                $item->quantity_received >= $item->quantity_ordered
            );

            $purchaseOrder->update([
                'status' => $allReceived ? PurchaseOrderStatus::RECEIVED : PurchaseOrderStatus::PARTIAL,
                'received_date' => $allReceived ? now() : null,
            ]);

            return $this->success(
                $purchaseOrder->load(['items', 'receipts.items']),
                'Items received successfully'
            );
        });
    }

    /**
     * Cancel purchase order
     */
    public function cancel(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if (in_array($purchaseOrder->status, [
            PurchaseOrderStatus::RECEIVED,
            PurchaseOrderStatus::CANCELLED,
        ])) {
            return $this->error('Cannot cancel this purchase order', 422);
        }

        $purchaseOrder->update(['status' => PurchaseOrderStatus::CANCELLED]);

        return $this->success($purchaseOrder, 'Purchase order cancelled');
    }
}
