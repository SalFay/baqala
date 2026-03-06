<?php

namespace App\Http\Controllers;

use App\Models\ProductSerial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductSerialController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Inventory/Serials/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = ProductSerial::with(['product', 'productVariant', 'order']);

        // Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('serial_number', 'like', "%{$request->search}%")
                    ->orWhere('imei', 'like', "%{$request->search}%")
                    ->orWhere('imei2', 'like', "%{$request->search}%")
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

        // Filter by warranty status
        if ($request->warranty_status === 'active') {
            $query->warrantyActive();
        } elseif ($request->warranty_status === 'expired') {
            $query->warrantyExpired();
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

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

        $serials = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($serial) => [
                'id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'imei' => $serial->imei,
                'imei2' => $serial->imei2,
                'product' => $serial->product ? [
                    'id' => $serial->product->id,
                    'name' => $serial->product->name,
                    'sku' => $serial->product->sku,
                ] : null,
                'product_variant' => $serial->productVariant ? [
                    'id' => $serial->productVariant->id,
                    'name' => $serial->productVariant->name,
                ] : null,
                'purchase_price' => $serial->purchase_price,
                'sale_price' => $serial->sale_price,
                'purchase_date' => $serial->purchase_date?->format('Y-m-d'),
                'sold_at' => $serial->sold_at?->format('Y-m-d H:i'),
                'status' => $serial->status,
                'status_color' => $serial->getStatusColor(),
                'color' => $serial->color,
                'storage_capacity' => $serial->storage_capacity,
                'warranty_end_date' => $serial->warranty_end_date?->format('Y-m-d'),
                'warranty_active' => $serial->isWarrantyActive(),
                'warranty_remaining_days' => $serial->getWarrantyRemainingDays(),
                'order_id' => $serial->order_id,
                'notes' => $serial->notes,
                'created_at' => $serial->created_at,
            ]);

        return response()->json([
            'data' => $serials,
            'total' => $total,
        ]);
    }

    public function forProduct(int $productId): JsonResponse
    {
        $serials = ProductSerial::where('product_id', $productId)
            ->available()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($serial) => [
                'id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'imei' => $serial->imei,
                'color' => $serial->color,
                'storage_capacity' => $serial->storage_capacity,
                'purchase_price' => $serial->purchase_price,
            ]);

        return response()->json(['data' => $serials]);
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $serial = ProductSerial::with(['product', 'productVariant'])
            ->where('serial_number', $request->query('query'))
            ->orWhere('imei', $request->query('query'))
            ->orWhere('imei2', $request->query('query'))
            ->first();

        if (!$serial) {
            return response()->json([
                'found' => false,
                'message' => 'Serial/IMEI not found',
            ]);
        }

        return response()->json([
            'found' => true,
            'data' => [
                'id' => $serial->id,
                'serial_number' => $serial->serial_number,
                'imei' => $serial->imei,
                'imei2' => $serial->imei2,
                'status' => $serial->status,
                'can_be_sold' => $serial->canBeSold(),
                'product' => $serial->product ? [
                    'id' => $serial->product->id,
                    'name' => $serial->product->name,
                    'sku' => $serial->product->sku,
                    'sale_price' => $serial->product->sale_price,
                ] : null,
                'product_variant' => $serial->productVariant ? [
                    'id' => $serial->productVariant->id,
                    'name' => $serial->productVariant->name,
                ] : null,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'serial_number' => 'required|string|max:255|unique:product_serials',
            'imei' => 'nullable|string|max:20|unique:product_serials',
            'imei2' => 'nullable|string|max:20|unique:product_serials',
            'purchase_id' => 'nullable|exists:purchase_orders,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_id' => 'nullable|integer',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date|after_or_equal:warranty_start_date',
            'color' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['status'] = ProductSerial::STATUS_AVAILABLE;

        $serial = ProductSerial::create($validated);

        return response()->json([
            'data' => $serial,
            'notifications' => [['type' => 'success', 'message' => 'Serial added successfully']],
        ], 201);
    }

    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'purchase_id' => 'nullable|exists:purchase_orders,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_id' => 'nullable|integer',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date|after_or_equal:warranty_start_date',
            'serials' => 'required|array|min:1',
            'serials.*.serial_number' => 'required|string|max:255|distinct|unique:product_serials',
            'serials.*.imei' => 'nullable|string|max:20|distinct|unique:product_serials',
            'serials.*.imei2' => 'nullable|string|max:20|distinct|unique:product_serials',
            'serials.*.color' => 'nullable|string|max:50',
            'serials.*.storage_capacity' => 'nullable|string|max:50',
        ]);

        $baseData = collect($validated)->except('serials')->toArray();
        $baseData['status'] = ProductSerial::STATUS_AVAILABLE;

        $created = [];
        foreach ($validated['serials'] as $serialData) {
            $created[] = ProductSerial::create(array_merge($baseData, $serialData));
        }

        return response()->json([
            'data' => $created,
            'count' => count($created),
            'notifications' => [['type' => 'success', 'message' => count($created) . ' serial(s) added successfully']],
        ], 201);
    }

    public function update(Request $request, ProductSerial $productSerial): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => 'sometimes|string|max:255|unique:product_serials,serial_number,' . $productSerial->id,
            'imei' => 'nullable|string|max:20|unique:product_serials,imei,' . $productSerial->id,
            'imei2' => 'nullable|string|max:20|unique:product_serials,imei2,' . $productSerial->id,
            'purchase_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'warranty_id' => 'nullable|integer',
            'warranty_start_date' => 'nullable|date',
            'warranty_end_date' => 'nullable|date',
            'color' => 'nullable|string|max:50',
            'storage_capacity' => 'nullable|string|max:50',
            'status' => 'sometimes|in:available,reserved,sold,returned,damaged,lost',
            'notes' => 'nullable|string|max:1000',
        ]);

        $productSerial->update($validated);

        return response()->json([
            'data' => $productSerial->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Serial updated successfully']],
        ]);
    }

    public function destroy(ProductSerial $productSerial): JsonResponse
    {
        if ($productSerial->isSold()) {
            return response()->json([
                'message' => 'Cannot delete a sold serial number.',
            ], 422);
        }

        $productSerial->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Serial deleted successfully']],
        ]);
    }

    public function updateStatus(Request $request, ProductSerial $productSerial): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:available,reserved,damaged,lost',
        ]);

        if ($productSerial->isSold()) {
            return response()->json([
                'message' => 'Cannot change status of a sold item. Use returns process instead.',
            ], 422);
        }

        $productSerial->update(['status' => $validated['status']]);

        return response()->json([
            'data' => $productSerial->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Status updated successfully']],
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $query = ProductSerial::query();

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        $stats = [
            'total' => $query->count(),
            'available' => (clone $query)->available()->count(),
            'reserved' => (clone $query)->reserved()->count(),
            'sold' => (clone $query)->sold()->count(),
            'returned' => (clone $query)->where('status', ProductSerial::STATUS_RETURNED)->count(),
            'damaged' => (clone $query)->where('status', ProductSerial::STATUS_DAMAGED)->count(),
            'lost' => (clone $query)->where('status', ProductSerial::STATUS_LOST)->count(),
            'warranty_expiring_soon' => (clone $query)
                ->whereNotNull('warranty_end_date')
                ->whereBetween('warranty_end_date', [now(), now()->addDays(30)])
                ->count(),
        ];

        return response()->json(['data' => $stats]);
    }
}
