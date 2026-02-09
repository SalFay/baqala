<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ReturnType;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Services\Return\ReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected ReturnService $returnService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $returns = OrderReturn::query()
            ->with(['order', 'customer', 'processedBy', 'items'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->type, fn($q, $type) => $q->where('type', $type))
            ->when($request->from_date, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->to_date, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
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
                $validated['notes'] ?? null
            );

            return response()->json($return, 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(OrderReturn $return): JsonResponse
    {
        return response()->json(
            $return->load(['order.items', 'customer', 'processedBy', 'items.product', 'items.variant', 'returnReason'])
        );
    }

    public function getReturnableItems(Order $order): JsonResponse
    {
        if (!$order->canBeReturned()) {
            return response()->json(['message' => 'Order cannot be returned'], 422);
        }

        $items = $this->returnService->getReturnableItems($order);

        return response()->json([
            'order' => $order->only(['id', 'order_number', 'total', 'created_at']),
            'items' => $items,
        ]);
    }

    public function approve(OrderReturn $return): JsonResponse
    {
        try {
            $return = $this->returnService->approveReturn($return);

            return response()->json([
                'message' => 'Return approved successfully',
                'return' => $return,
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
                'return' => $return,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function process(Request $request, OrderReturn $return): JsonResponse
    {
        $validated = $request->validate([
            'refund_method' => 'required|string',
            'restocking_fee' => 'nullable|numeric|min:0',
        ]);

        try {
            $return = $this->returnService->processReturn(
                $return,
                $validated['refund_method'],
                $validated['restocking_fee'] ?? 0
            );

            return response()->json([
                'message' => 'Return processed successfully',
                'return' => $return,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
