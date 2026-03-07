<?php

namespace App\Http\Controllers;

use App\Http\Resources\KitchenOrderResource;
use App\Models\KitchenOrder;
use App\Services\KitchenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KitchenController extends Controller
{
    public function __construct(
        protected KitchenService $kitchenService
    ) {}

    public function display(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->getPendingOrders($request);
        }

        return Inertia::render('Kitchen/Display');
    }

    public function getPendingOrders(Request $request): JsonResponse
    {
        $station = $request->station;
        $pendingOrders = $this->kitchenService->getPendingOrders($station);
        $readyOrders = $this->kitchenService->getReadyOrders();
        $stats = $this->kitchenService->getStatistics();

        return response()->json([
            'pending' => $this->formatOrderGroups($pendingOrders),
            'ready' => $this->formatOrderGroups($readyOrders),
            'statistics' => $stats,
            'stations' => $this->kitchenService->getStations(),
        ]);
    }

    protected function formatOrderGroups($groups): array
    {
        $result = [];

        foreach ($groups as $orderId => $items) {
            $firstItem = $items->first();
            $order = $firstItem->order;

            $result[] = [
                'order_id' => $orderId,
                'order_number' => $order->order_number,
                'table' => $order->restaurantTable?->name,
                'customer' => $order->customer?->full_name ?? 'Walk-in',
                'created_at' => $order->created_at->format('H:i'),
                'elapsed_time' => $order->created_at->diffInMinutes(now()),
                'items' => KitchenOrderResource::collection($items),
            ];
        }

        return $result;
    }

    public function updateStatus(Request $request, KitchenOrder $kitchenOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,preparing,ready,served,cancelled',
        ]);

        $kitchenOrder = $this->kitchenService->updateOrderStatus(
            $kitchenOrder->id,
            $validated['status']
        );

        return response()->json([
            'data' => new KitchenOrderResource($kitchenOrder),
            'notifications' => [['type' => 'success', 'message' => 'Status updated']],
        ]);
    }

    public function startPreparing(KitchenOrder $kitchenOrder): JsonResponse
    {
        if (!$kitchenOrder->startPreparing()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot start preparing this item']],
            ], 422);
        }

        return response()->json([
            'data' => new KitchenOrderResource($kitchenOrder->fresh()),
            'notifications' => [['type' => 'info', 'message' => 'Started preparing']],
        ]);
    }

    public function markReady(KitchenOrder $kitchenOrder): JsonResponse
    {
        if (!$kitchenOrder->markReady()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot mark as ready']],
            ], 422);
        }

        return response()->json([
            'data' => new KitchenOrderResource($kitchenOrder->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Item ready']],
        ]);
    }

    public function markServed(KitchenOrder $kitchenOrder): JsonResponse
    {
        if (!$kitchenOrder->markServed()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot mark as served']],
            ], 422);
        }

        return response()->json([
            'data' => new KitchenOrderResource($kitchenOrder->fresh()),
        ]);
    }

    public function bumpOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        $count = $this->kitchenService->markOrderReady($validated['order_id']);

        return response()->json([
            'bumped_count' => $count,
            'notifications' => [['type' => 'success', 'message' => "{$count} items marked as ready"]],
        ]);
    }

    public function statistics(): JsonResponse
    {
        return response()->json($this->kitchenService->getStatistics());
    }

    public function byStation(Request $request): JsonResponse
    {
        $station = $request->station;
        $orders = $this->kitchenService->getOrdersByStation($station);

        return response()->json([
            'data' => KitchenOrderResource::collection($orders),
        ]);
    }
}
