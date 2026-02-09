<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user', 'store'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->payment_status, fn($q, $status) => $q->where('payment_status', $status))
            ->when($request->from_date, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($request->to_date, fn($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->when($request->search, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('order_number', 'like', "%{$term}%")
                        ->orWhere('invoice_no', 'like', "%{$term}%")
                        ->orWhere('customer_name', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($request->per_page ?? 20);

        return response()->json($orders);
    }

    public function show(Order $order): JsonResponse
    {
        return response()->json(
            $order->load(['items.product', 'items.variant', 'customer', 'user', 'store', 'payments', 'returns'])
        );
    }

    public function receipt(Order $order): JsonResponse
    {
        return response()->json($this->orderService->getOrderReceipt($order));
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $order = $this->orderService->cancelOrder($order, $validated['reason'] ?? null);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->load(['items', 'customer']),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function todayOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user'])
            ->whereDate('created_at', today())
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', OrderStatus::COMPLETED)->count(),
            'total_sales' => $orders->where('status', OrderStatus::COMPLETED)->sum('total'),
        ];

        return response()->json([
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function recentOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json($orders);
    }
}
