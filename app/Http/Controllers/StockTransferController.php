<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\StockTransfer\StoreStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\Product;
use App\Models\StockTransfer;
use App\Models\Store;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StockTransferController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('StockTransfers/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            StockTransfer::class,
            with: ['fromStore', 'toStore', 'currentStatus'],
            resource: StockTransferResource::class,
            options: [
                'searchColumns' => ['transfer_number'],
                'filterColumns' => [
                    'from_store_id' => 'exact',
                    'to_store_id' => 'exact',
                ],
                'withCount' => ['items'],
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
        return Inertia::render('StockTransfers/Create', [
            'stores' => Store::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'products' => Product::where('is_active', true)->orderBy('name')->get(['id', 'name', 'sku']),
        ]);
    }

    public function store(StoreStockTransferRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();

        $transfer = StockTransfer::create([
            'from_store_id' => $data['from_store_id'],
            'to_store_id' => $data['to_store_id'],
            'notes' => $data['notes'] ?? null,
            'created_by_id' => auth()->id(),
        ]);

        foreach ($data['items'] as $item) {
            $transfer->items()->create($item);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new StockTransferResource($transfer),
                'notifications' => [['type' => 'success', 'message' => 'Stock transfer created']],
            ], 201);
        }

        return redirect()->route('stock-transfers.show', $transfer)->with('success', 'Stock transfer created.');
    }

    public function show(StockTransfer $stockTransfer, Request $request): Response|JsonResponse
    {
        $stockTransfer->load(['fromStore', 'toStore', 'items.product', 'currentStatus', 'statusHistories.status']);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new StockTransferResource($stockTransfer),
            ]);
        }

        return Inertia::render('StockTransfers/Show', [
            'transfer' => new StockTransferResource($stockTransfer),
        ]);
    }

    public function ship(Request $request, StockTransfer $stockTransfer): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new StockTransferResource($stockTransfer->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Transfer shipped']],
            ]);
        }

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer shipped.');
    }

    public function receive(Request $request, StockTransfer $stockTransfer): RedirectResponse|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new StockTransferResource($stockTransfer->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Transfer received']],
            ]);
        }

        return redirect()->route('stock-transfers.show', $stockTransfer)->with('success', 'Transfer received.');
    }
}
