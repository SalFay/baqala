<?php

namespace App\Http\Controllers;

use App\Enums\ReturnType;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReturnReason;
use App\Models\Store;
use App\Services\Return\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    /**
     * Server-side listing for DataGridTable
     */
    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        $query = OrderReturn::with(['order', 'customer', 'processedBy'])
            ->where('store_id', $store->id);

        // Search
        if ($request->search) {
            $query->where('return_number', 'like', "%{$request->search}%");
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderByDesc('created_at');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $returns = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($return) => [
                'id' => $return->id,
                'return_number' => $return->return_number,
                'order_number' => $return->order?->order_number,
                'customer' => $return->customer?->full_name,
                'type' => $return->type,
                'status' => $return->status,
                'refund_amount' => $return->refund_amount,
                'created_at' => $return->created_at,
            ]);

        return response()->json([
            'data' => $returns,
            'total' => $total,
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $store = Store::first();

        $returns = OrderReturn::query()
            ->with(['order', 'customer', 'processedBy', 'items'])
            ->where('store_id', $store->id)
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->from_date, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->to_date, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($request->search, fn($q, $term) => $q->where('return_number', 'like', "%{$term}%"))
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($returns);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'type' => 'required|in:refund,exchange,store_credit',
            'return_reason_id' => 'nullable|exists:return_reasons,id',
            'reason' => 'nullable|string',
            'notes' => 'nullable|string',
            'refund_method' => 'nullable|string',
            'restocking_fee' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.order_item_id' => 'required|exists:order_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.condition' => 'nullable|in:sellable,damaged,defective',
            'items.*.restock' => 'nullable|boolean',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);

            $return = $this->returnService->createReturn(
                $order,
                $validated['items'],
                ReturnType::from($validated['type']),
                $validated['return_reason_id'] ?? null,
                $validated['reason'] ?? null,
                $validated['notes'] ?? null,
                $validated['refund_method'] ?? null,
                $validated['restocking_fee'] ?? 0
            );

            return response()->json([
                'message' => 'Return created successfully',
                'data' => $return->load(['order', 'customer', 'items']),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(OrderReturn $return): JsonResponse
    {
        return response()->json([
            'data' => $return->load(['order.items', 'customer', 'processedBy', 'items.product', 'items.variant', 'returnReason']),
        ]);
    }

    public function getReturnableItems(Order $order): JsonResponse
    {
        if (!$order->canBeReturned()) {
            return response()->json(['message' => 'Order cannot be returned'], 422);
        }

        $items = $this->returnService->getReturnableItems($order);

        return response()->json([
            'data' => [
                'order' => $order->only(['id', 'order_number', 'total', 'created_at']),
                'items' => $items,
            ],
        ]);
    }

    public function approve(OrderReturn $return): JsonResponse
    {
        try {
            $return = $this->returnService->approveReturn($return);

            return response()->json([
                'message' => 'Return approved successfully',
                'data' => $return->load(['order', 'customer', 'items']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, OrderReturn $return): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string',
        ]);

        try {
            $return = $this->returnService->rejectReturn($return, $validated['reason'] ?? null);

            return response()->json([
                'message' => 'Return rejected',
                'data' => $return->load(['order', 'customer', 'items']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function process(Request $request, OrderReturn $return): JsonResponse
    {
        $validated = $request->validate([
            'refund_method' => 'nullable|string',
            'restocking_fee' => 'nullable|numeric|min:0',
        ]);

        try {
            $return = $this->returnService->processReturn(
                $return,
                $validated['refund_method'] ?? $return->refund_method ?? 'cash',
                $validated['restocking_fee'] ?? 0
            );

            return response()->json([
                'message' => 'Return processed successfully',
                'data' => $return->load(['order', 'customer', 'items']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reasons(): JsonResponse
    {
        $reasons = ReturnReason::where('is_active', true)
            ->orderBy('sort_order')
            ->get(['id', 'name', 'name_ar', 'requires_note']);

        return response()->json(['data' => $reasons]);
    }
}
