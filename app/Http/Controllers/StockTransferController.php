<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController extends Controller
{
    public function index(Request $request): Response
    {
        $transfers = StockTransfer::query()
            ->with(['fromStore', 'toStore', 'currentStatus'])
            ->when($request->status, fn($q, $status) => $q->whereStatus($status))
            ->when($request->search, function ($q, $term) {
                $q->where('transfer_number', 'like', "%{$term}%");
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return Inertia::render('StockTransfers/Index', [
            'transfers' => [
                'data' => $transfers->map(fn($t) => [
                    'id' => $t->id,
                    'transfer_number' => $t->transfer_number,
                    'from_store' => $t->fromStore?->name,
                    'to_store' => $t->toStore?->name,
                    'status' => $t->currentStatus?->code ?? $t->status,
                    'items_count' => $t->items_count,
                    'created_at' => $t->created_at,
                ]),
                'meta' => [
                    'total' => $transfers->total(),
                    'per_page' => $transfers->perPage(),
                    'current_page' => $transfers->currentPage(),
                ],
            ],
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        $stores = Store::where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'name', 'sku']);

        return Inertia::render('StockTransfers/Create', [
            'stores' => $stores,
            'products' => $products,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id|different:from_store_id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $transfer = StockTransfer::create([
            'from_store_id' => $validated['from_store_id'],
            'to_store_id' => $validated['to_store_id'],
            'notes' => $validated['notes'],
            'created_by_id' => auth()->id(),
        ]);

        foreach ($validated['items'] as $item) {
            $transfer->items()->create($item);
        }

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created.');
    }

    public function show(StockTransfer $stockTransfer): Response
    {
        $stockTransfer->load(['fromStore', 'toStore', 'items.product', 'currentStatus', 'statusHistories.status']);

        return Inertia::render('StockTransfers/Show', [
            'transfer' => [
                'id' => $stockTransfer->id,
                'transfer_number' => $stockTransfer->transfer_number,
                'from_store' => $stockTransfer->fromStore,
                'to_store' => $stockTransfer->toStore,
                'status' => $stockTransfer->currentStatus?->code ?? $stockTransfer->status,
                'notes' => $stockTransfer->notes,
                'items' => $stockTransfer->items->map(fn($item) => [
                    'id' => $item->id,
                    'product' => $item->product?->name,
                    'quantity' => $item->quantity,
                    'shipped_quantity' => $item->shipped_quantity,
                    'received_quantity' => $item->received_quantity,
                ]),
                'created_at' => $stockTransfer->created_at,
            ],
        ]);
    }

    public function ship(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.shipped_quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            $item = $stockTransfer->items()->find($itemData['id']);
            if ($item) {
                $item->update(['shipped_quantity' => $itemData['shipped_quantity']]);
            }
        }

        $stockTransfer->changeStatus('in_transit', 'Items shipped');

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer shipped.');
    }

    public function receive(Request $request, StockTransfer $stockTransfer): RedirectResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:stock_transfer_items,id',
            'items.*.received_quantity' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $itemData) {
            $item = $stockTransfer->items()->find($itemData['id']);
            if ($item) {
                $item->update(['received_quantity' => $itemData['received_quantity']]);
            }
        }

        $stockTransfer->changeStatus('completed', 'Items received');

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer received.');
    }
}
