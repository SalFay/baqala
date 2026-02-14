<?php

namespace App\Http\Controllers\Api;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\CancelOrderRequest;
use App\Http\Requests\Api\Order\UpdateStatusRequest;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\StatusHistoryResource;
use App\Http\Resources\StatusResource;
use App\Models\Order;
use App\Models\Status;
use App\Services\Order\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user', 'store', 'currentStatus'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->customer_id, fn($q, $id) => $q->where('customer_id', $id))
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->status_code, fn($q, $code) => $q->whereStatus($code))
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

        return OrderResource::collection($orders)->response();
    }

    public function show(Order $order): JsonResponse
    {
        return OrderResource::make(
            $order->load([
                'items.product',
                'items.productVariant',
                'customer',
                'user',
                'store',
                'payments.paymentMethod',
                'returns',
                'currentStatus',
                'statusHistories.status',
                'statusHistories.previousStatus',
                'statusHistories.user',
                'createdBy',
                'updatedBy',
            ])
        )->response();
    }

    public function receipt(Order $order): JsonResponse
    {
        return response()->json($this->orderService->getOrderReceipt($order));
    }

    public function cancel(CancelOrderRequest $request, Order $order): JsonResponse
    {
        try {
            $order = $this->orderService->cancelOrder($order, $request->validated('reason'));

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => OrderResource::make($order->load(['items', 'customer', 'currentStatus'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function updateStatus(UpdateStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $order->changeStatus($request->validated('status'), $request->validated('reason'));

            return response()->json([
                'message' => 'Status updated successfully',
                'order' => OrderResource::make($order->fresh(['currentStatus', 'statusHistories.status'])),
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function statusHistory(Order $order): JsonResponse
    {
        $histories = $order->statusHistories()
            ->with(['status', 'previousStatus', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return StatusHistoryResource::collection($histories)->response();
    }

    public function activityLog(Order $order): JsonResponse
    {
        $activities = Activity::where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->with('causer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return ActivityLogResource::collection($activities)->response();
    }

    public function availableStatuses(Order $order): JsonResponse
    {
        return response()->json([
            'current_status' => StatusResource::make($order->currentStatus),
            'available_statuses' => StatusResource::collection($order->getAllowedNextStatuses()),
        ]);
    }

    public function todayOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user', 'currentStatus'])
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
            'orders' => OrderResource::collection($orders),
            'stats' => $stats,
        ]);
    }

    public function recentOrders(Request $request): JsonResponse
    {
        $orders = Order::query()
            ->with(['customer', 'user', 'currentStatus'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->limit($request->limit ?? 10)
            ->get();

        return OrderResource::collection($orders)->response();
    }
}
