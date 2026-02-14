<?php

namespace App\Http\Controllers;
use App\Http\Requests\Api\Order\UpdateStatusRequest;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\StatusHistoryResource;
use App\Http\Resources\StatusResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class OrderController extends Controller
{
    public function index(Request $request): Response
    {
        $orders = Order::query()
            ->with(['customer', 'user', 'store', 'currentStatus'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->when($request->status, fn($q, $status) => $q->whereStatus($status))
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

        return Inertia::render('Orders/Index', [
            'orders' => [
                'data' => $orders->map(fn($order) => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'invoice_no' => $order->invoice_no,
                    'customer_name' => $order->customer?->full_name ?? $order->customer_name ?? 'Walk-in',
                    'total' => $order->total,
                    'current_status' => $order->currentStatus?->code ?? $order->status,
                    'payment_status' => $order->payment_status,
                    'created_at' => $order->created_at,
                ]),
                'meta' => [
                    'total' => $orders->total(),
                    'per_page' => $orders->perPage(),
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                ],
            ],
            'filters' => $request->only(['search', 'status', 'payment_status', 'from_date', 'to_date']),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'items.product',
            'items.productVariant',
            'customer',
            'user',
            'store',
            'payments.paymentMethod',
            'currentStatus',
            'createdBy',
            'updatedBy',
        ]);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'invoice_no' => $order->invoice_no,
                'customer' => $order->customer ? [
                    'id' => $order->customer->id,
                    'full_name' => $order->customer->full_name,
                ] : null,
                'customer_name' => $order->customer_name,
                'user' => $order->user ? [
                    'id' => $order->user->id,
                    'first_name' => $order->user->first_name,
                ] : null,
                'cashier_name' => $order->cashier_name,
                'subtotal' => $order->subtotal,
                'discount' => $order->discount,
                'tax_amount' => $order->tax_amount,
                'total' => $order->total,
                'current_status' => $order->currentStatus?->code ?? $order->status,
                'payment_status' => $order->payment_status,
                'items' => $order->items->map(fn($item) => [
                    'id' => $item->id,
                    'product_name' => $item->product?->name ?? $item->product_name,
                    'sku' => $item->product?->sku ?? $item->sku,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'line_total' => $item->line_total,
                ]),
                'payments' => $order->payments->map(fn($payment) => [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'method' => $payment->paymentMethod?->name ?? $payment->payment_method,
                ]),
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ],
        ]);
    }

    public function updateStatus(UpdateStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $order->changeStatus($request->validated('status'), $request->validated('reason'));

            return response()->json([
                'message' => 'Status updated successfully',
                'order' => [
                    'id' => $order->id,
                    'current_status' => $order->fresh(['currentStatus'])->currentStatus?->code,
                ],
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
}
