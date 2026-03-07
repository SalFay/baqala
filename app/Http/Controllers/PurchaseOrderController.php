<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\VendorResource;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\Vendor;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('PurchaseOrders/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            PurchaseOrder::class,
            with: ['vendor', 'store', 'currentStatus'],
            resource: PurchaseOrderResource::class,
            options: [
                'searchColumns' => ['po_number'],
                'filterColumns' => [
                    'vendor_id' => 'exact',
                    'store_id' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query, $request) {
                    if ($request->status) {
                        $query->whereStatus($request->status);
                    }
                },
            ]
        );
    }

    public function create(): Response
    {
        return Inertia::render('PurchaseOrders/Create', [
            'vendors' => VendorResource::collection(Vendor::where('is_active', true)->orderBy('name')->get()),
            'stores' => Store::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']),
        ]);
    }

    public function store(StorePurchaseOrderRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $po = PurchaseOrder::create([
            'vendor_id' => $data['vendor_id'],
            'store_id' => $data['store_id'],
            'expected_date' => $data['expected_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by_id' => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $po->items()->create($item);
        }

        $po->calculateTotals();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new PurchaseOrderResource($po),
                'notifications' => [['type' => 'success', 'message' => 'Purchase order created']],
            ], 201);
        }

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder, Request $request): Response|JsonResponse
    {
        $purchaseOrder->load(['vendor', 'store', 'items.product', 'currentStatus', 'statusHistories.status']);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new PurchaseOrderResource($purchaseOrder),
            ]);
        }

        return Inertia::render('PurchaseOrders/Show', [
            'order' => new PurchaseOrderResource($purchaseOrder),
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['items.product']);

        return Inertia::render('PurchaseOrders/Edit', [
            'order' => new PurchaseOrderResource($purchaseOrder),
            'vendors' => VendorResource::collection(Vendor::where('is_active', true)->orderBy('name')->get()),
            'stores' => Store::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']),
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'sometimes|exists:vendors,id',
            'store_id' => 'sometimes|exists:stores,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new PurchaseOrderResource($purchaseOrder->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Purchase order updated']],
            ]);
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            $item = $purchaseOrder->items()->find($itemData['id']);
            if ($item) {
                $item->update(['received_quantity' => $itemData['received_quantity']]);
            }
        }

        $purchaseOrder->checkReceiptStatus();

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new PurchaseOrderResource($purchaseOrder->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Items received successfully']],
            ]);
        }

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Items received successfully.');
    }
}
