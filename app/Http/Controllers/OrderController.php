<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Order\UpdateStatusRequest;
use App\Http\Resources\ActivityLogResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\StatusHistoryResource;
use App\Http\Resources\StatusResource;
use App\Models\Order;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class OrderController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Orders/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Order::class,
            with: ['customer', 'user', 'store', 'currentStatus'],
            resource: OrderResource::class,
            options: [
                'searchColumns' => ['order_number', 'invoice_no', 'customer_name'],
                'filterColumns' => [
                    'store_id' => 'exact',
                    'payment_status' => 'exact',
                    'location_id' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query, $request) {
                    if ($request->status) {
                        $query->whereStatus($request->status);
                    }
                    if ($request->from_date) {
                        $query->whereDate('created_at', '>=', $request->from_date);
                    }
                    if ($request->to_date) {
                        $query->whereDate('created_at', '<=', $request->to_date);
                    }
                },
            ]
        );
    }

    public function show(Order $order, Request $request): Response|JsonResponse
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

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new OrderResource($order),
            ]);
        }

        return Inertia::render('Orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateStatusRequest $request, Order $order): JsonResponse
    {
        try {
            $order->changeStatus($request->validated('status'), $request->validated('reason'));

            return response()->json([
                'data' => new OrderResource($order->fresh(['currentStatus'])),
                'notifications' => [['type' => 'success', 'message' => 'Status updated successfully']],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    public function statusHistory(Order $order): JsonResponse
    {
        $histories = $order->statusHistories()
            ->with(['status', 'previousStatus', 'user'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'data' => StatusHistoryResource::collection($histories),
        ]);
    }

    public function activityLog(Order $order): JsonResponse
    {
        $activities = Activity::where('subject_type', Order::class)
            ->where('subject_id', $order->id)
            ->with('causer')
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json([
            'data' => ActivityLogResource::collection($activities),
            'total' => $activities->total(),
        ]);
    }

    public function availableStatuses(Order $order): JsonResponse
    {
        return response()->json([
            'current_status' => new StatusResource($order->currentStatus),
            'available_statuses' => StatusResource::collection($order->getAllowedNextStatuses()),
        ]);
    }
}
