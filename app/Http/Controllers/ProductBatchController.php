<?php

namespace App\Http\Controllers;

use App\Models\ProductBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductBatchController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Inventory/Batches/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = ProductBatch::with(['product', 'productVariant']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('batch_number', 'like', "%{$request->search}%")
                    ->orWhere('lot_number', 'like', "%{$request->search}%")
                    ->orWhereHas('product', function ($q) use ($request) {
                        $q->where('name', 'like', "%{$request->search}%");
                    });
            });
        }

        // Filter by status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter by product
        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by expiry status
        if ($request->expiry_status === 'expired') {
            $query->expired();
        } elseif ($request->expiry_status === 'expiring_soon') {
            $query->expiringSoon($request->expiry_days ?? 30);
        } elseif ($request->expiry_status === 'valid') {
            $query->notExpired();
        }

        // Filter by in stock
        if ($request->in_stock === 'true') {
            $query->inStock();
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('expiry_date', 'asc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $batches = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'lot_number' => $batch->lot_number,
                'product' => $batch->product ? [
                    'id' => $batch->product->id,
                    'name' => $batch->product->name,
                    'sku' => $batch->product->sku,
                ] : null,
                'product_variant' => $batch->productVariant ? [
                    'id' => $batch->productVariant->id,
                    'name' => $batch->productVariant->name,
                ] : null,
                'manufacturing_date' => $batch->manufacturing_date?->format('Y-m-d'),
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_until_expiry' => $batch->getDaysUntilExpiry(),
                'is_expired' => $batch->isExpired(),
                'expiry_status_color' => $batch->getExpiryStatusColor(),
                'shelf_life_percentage' => $batch->getShelfLifePercentage(),
                'purchase_price' => $batch->purchase_price,
                'quantity_purchased' => (float) $batch->quantity_purchased,
                'quantity_available' => (float) $batch->quantity_available,
                'quantity_sold' => (float) $batch->quantity_sold,
                'quantity_damaged' => (float) $batch->quantity_damaged,
                'quantity_expired' => (float) $batch->quantity_expired,
                'status' => $batch->status,
                'status_color' => $batch->getStatusColor(),
                'can_be_sold' => $batch->canBeSold(),
                'received_date' => $batch->received_date?->format('Y-m-d'),
                'notes' => $batch->notes,
                'created_at' => $batch->created_at,
            ]);

        return response()->json([
            'data' => $batches,
            'total' => $total,
        ]);
    }

    public function forProduct(int $productId): JsonResponse
    {
        $batches = ProductBatch::where('product_id', $productId)
            ->fefo()
            ->get()
            ->map(fn($batch) => [
                'id' => $batch->id,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_until_expiry' => $batch->getDaysUntilExpiry(),
                'quantity_available' => (float) $batch->quantity_available,
                'can_be_sold' => $batch->canBeSold(),
            ]);

        return response()->json(['data' => $batches]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'batch_number' => 'required|string|max:255',
            'lot_number' => 'nullable|string|max:255',
            'manufacturing_date' => 'nullable|date|before_or_equal:today',
            'expiry_date' => 'required|date|after:today',
            'purchase_id' => 'nullable|exists:purchase_orders,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
            'quantity_purchased' => 'required|numeric|min:0.0001',
            'supplier_batch_ref' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate batch number for the same product
        $exists = ProductBatch::where('product_id', $validated['product_id'])
            ->where('batch_number', $validated['batch_number'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A batch with this number already exists for this product.',
            ], 422);
        }

        $validated['quantity_available'] = $validated['quantity_purchased'];
        $validated['status'] = ProductBatch::STATUS_ACTIVE;

        $batch = ProductBatch::create($validated);

        return response()->json([
            'data' => $batch,
            'notifications' => [['type' => 'success', 'message' => 'Batch created successfully']],
        ], 201);
    }

    public function update(Request $request, ProductBatch $productBatch): JsonResponse
    {
        $validated = $request->validate([
            'batch_number' => 'sometimes|string|max:255',
            'lot_number' => 'nullable|string|max:255',
            'manufacturing_date' => 'nullable|date',
            'expiry_date' => 'sometimes|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'received_date' => 'nullable|date',
            'quantity_available' => 'sometimes|numeric|min:0',
            'supplier_batch_ref' => 'nullable|string|max:255',
            'status' => 'sometimes|in:active,low_stock,out_of_stock,expired,recalled,quarantine',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate batch number
        if (isset($validated['batch_number']) && $validated['batch_number'] !== $productBatch->batch_number) {
            $exists = ProductBatch::where('product_id', $productBatch->product_id)
                ->where('batch_number', $validated['batch_number'])
                ->where('id', '!=', $productBatch->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'message' => 'A batch with this number already exists for this product.',
                ], 422);
            }
        }

        $productBatch->update($validated);

        return response()->json([
            'data' => $productBatch->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Batch updated successfully']],
        ]);
    }

    public function destroy(ProductBatch $productBatch): JsonResponse
    {
        if ($productBatch->quantity_sold > 0) {
            return response()->json([
                'message' => 'Cannot delete a batch that has sold items. Mark it as expired instead.',
            ], 422);
        }

        $productBatch->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Batch deleted successfully']],
        ]);
    }

    public function markAsExpired(ProductBatch $productBatch): JsonResponse
    {
        $productBatch->markAsExpired();

        return response()->json([
            'data' => $productBatch->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Batch marked as expired']],
        ]);
    }

    public function recall(ProductBatch $productBatch): JsonResponse
    {
        $productBatch->recall();

        return response()->json([
            'data' => $productBatch->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Batch recalled']],
        ]);
    }

    public function quarantine(ProductBatch $productBatch): JsonResponse
    {
        $productBatch->quarantine();

        return response()->json([
            'data' => $productBatch->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Batch placed in quarantine']],
        ]);
    }

    public function adjustQuantity(Request $request, ProductBatch $productBatch): JsonResponse
    {
        $validated = $request->validate([
            'adjustment' => 'required|numeric',
            'reason' => 'required|in:damaged,correction,count,other',
            'notes' => 'nullable|string|max:500',
        ]);

        $newQuantity = $productBatch->quantity_available + $validated['adjustment'];

        if ($newQuantity < 0) {
            return response()->json([
                'message' => 'Adjustment would result in negative quantity.',
            ], 422);
        }

        if ($validated['reason'] === 'damaged' && $validated['adjustment'] < 0) {
            $productBatch->quantity_damaged += abs($validated['adjustment']);
        }

        $productBatch->quantity_available = $newQuantity;

        // Update status
        if ($newQuantity <= 0) {
            $productBatch->status = ProductBatch::STATUS_OUT_OF_STOCK;
        } elseif ($newQuantity <= ($productBatch->quantity_purchased * 0.2)) {
            $productBatch->status = ProductBatch::STATUS_LOW_STOCK;
        } else {
            $productBatch->status = ProductBatch::STATUS_ACTIVE;
        }

        $productBatch->save();

        return response()->json([
            'data' => $productBatch->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Quantity adjusted successfully']],
        ]);
    }

    public function expiryReport(Request $request): JsonResponse
    {
        $days = $request->days ?? 30;

        $expiring = ProductBatch::with('product')
            ->expiringSoon($days)
            ->inStock()
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($batch) => [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product->name,
                'product_sku' => $batch->product->sku,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_until_expiry' => $batch->getDaysUntilExpiry(),
                'quantity_available' => (float) $batch->quantity_available,
                'purchase_price' => $batch->purchase_price,
                'potential_loss' => $batch->purchase_price ? $batch->quantity_available * $batch->purchase_price : null,
            ]);

        $expired = ProductBatch::with('product')
            ->expired()
            ->where('quantity_available', '>', 0)
            ->orderBy('expiry_date', 'desc')
            ->limit(50)
            ->get()
            ->map(fn($batch) => [
                'id' => $batch->id,
                'product_id' => $batch->product_id,
                'product_name' => $batch->product->name,
                'product_sku' => $batch->product->sku,
                'batch_number' => $batch->batch_number,
                'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                'days_expired' => abs($batch->getDaysUntilExpiry()),
                'quantity_available' => (float) $batch->quantity_available,
                'purchase_price' => $batch->purchase_price,
                'potential_loss' => $batch->purchase_price ? $batch->quantity_available * $batch->purchase_price : null,
            ]);

        return response()->json([
            'expiring' => $expiring,
            'expiring_count' => $expiring->count(),
            'expiring_value' => $expiring->sum('potential_loss'),
            'expired' => $expired,
            'expired_count' => $expired->count(),
            'expired_value' => $expired->sum('potential_loss'),
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $query = ProductBatch::query();

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $total = (clone $query)->count();
        $totalQuantity = (clone $query)->sum('quantity_available');

        $stats = [
            'total_batches' => $total,
            'total_quantity' => (float) $totalQuantity,
            'active' => (clone $query)->where('status', ProductBatch::STATUS_ACTIVE)->count(),
            'low_stock' => (clone $query)->where('status', ProductBatch::STATUS_LOW_STOCK)->count(),
            'out_of_stock' => (clone $query)->where('status', ProductBatch::STATUS_OUT_OF_STOCK)->count(),
            'expired' => (clone $query)->expired()->count(),
            'recalled' => (clone $query)->where('status', ProductBatch::STATUS_RECALLED)->count(),
            'quarantine' => (clone $query)->where('status', ProductBatch::STATUS_QUARANTINE)->count(),
            'expiring_7_days' => (clone $query)->expiringWithinDays(7)->count(),
            'expiring_30_days' => (clone $query)->expiringWithinDays(30)->count(),
            'expiring_90_days' => (clone $query)->expiringWithinDays(90)->count(),
        ];

        return response()->json(['data' => $stats]);
    }
}
