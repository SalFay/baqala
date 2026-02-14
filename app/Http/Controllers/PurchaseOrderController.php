<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PurchaseOrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = PurchaseOrder::query()
            ->with(['vendor', 'store', 'currentStatus'])
            ->when($request->vendor_id, fn($q, $id) => $q->where('vendor_id', $id))
            ->when($request->status, fn($q, $status) => $q->whereStatus($status))
            ->when($request->search, function ($q, $term) {
                $q->where('po_number', 'like', "%{$term}%");
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return Inertia::render('PurchaseOrders/Index', [
            'orders' => [
                'data' => $orders->map(fn($po) => [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'vendor' => $po->vendor?->name,
                    'store' => $po->store?->name,
                    'total' => $po->total,
                    'status' => $po->currentStatus?->code ?? $po->status,
                    'expected_date' => $po->expected_date,
                    'created_at' => $po->created_at,
                ]),
                'meta' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                ],
            ],
            'filters' => $request->only(['search', 'vendor_id', 'status']),
        ]);
    }

    public function create(): Response
    {
        $vendors = Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $stores = Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']);

        return Inertia::render('PurchaseOrders/Create', [
            'vendors' => $vendors,
            'stores' => $stores,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'store_id' => 'required|exists:stores,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        $po = PurchaseOrder::create([
            'vendor_id' => $validated['vendor_id'],
            'store_id' => $validated['store_id'],
            'expected_date' => $validated['expected_date'],
            'notes' => $validated['notes'],
            'created_by_id' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $po->items()->create($item);
        }

        $po->calculateTotals();

        return redirect()->route('purchase-orders.show', $po)->with('success', 'Purchase order created.');
    }

    public function show(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['vendor', 'store', 'items.product', 'currentStatus', 'statusHistories.status']);

        return Inertia::render('PurchaseOrders/Show', [
            'order' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'vendor' => $purchaseOrder->vendor,
                'store' => $purchaseOrder->store,
                'status' => $purchaseOrder->currentStatus?->code ?? $purchaseOrder->status,
                'subtotal' => $purchaseOrder->subtotal,
                'tax_amount' => $purchaseOrder->tax_amount,
                'total' => $purchaseOrder->total,
                'expected_date' => $purchaseOrder->expected_date,
                'notes' => $purchaseOrder->notes,
                'items' => $purchaseOrder->items->map(fn($item) => [
                    'id' => $item->id,
                    'product' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'received_quantity' => $item->received_quantity,
                    'unit_cost' => $item->unit_cost,
                    'total' => $item->total,
                ]),
                'created_at' => $purchaseOrder->created_at,
            ],
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): Response
    {
        $purchaseOrder->load(['items.product']);

        $vendors = Vendor::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $stores = Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku', 'cost_price']);

        return Inertia::render('PurchaseOrders/Edit', [
            'order' => $purchaseOrder,
            'vendors' => $vendors,
            'stores' => $stores,
            'products' => $products,
        ]);
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'store_id' => 'required|exists:stores,id',
            'expected_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Purchase order updated.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
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

        return redirect()->route('purchase-orders.show', $purchaseOrder)->with('success', 'Items received successfully.');
    }
}
