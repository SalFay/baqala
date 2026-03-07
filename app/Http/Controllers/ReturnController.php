<?php

namespace App\Http\Controllers;

use App\Enums\ReturnType;
use App\Http\Requests\Api\Return\StoreReturnRequest;
use App\Http\Resources\ReturnResource;
use App\Models\Order;
use App\Models\OrderReturn;
use App\Models\ReturnReason;
use App\Models\Store;
use App\Services\Return\ReturnService;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    use HasListing;

    public function __construct(
        protected ReturnService $returnService
    ) {}

    public function index(Request $request): JsonResponse
    {
        if ($request->has('pageSize')) {
            return $this->listing($request);
        }

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

        return response()->json([
            'data' => ReturnResource::collection($returns),
            'total' => $returns->total(),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        return $this->getListing(
            $request,
            OrderReturn::class,
            with: ['order', 'customer', 'processedBy'],
            resource: ReturnResource::class,
            options: [
                'searchColumns' => ['return_number'],
                'filterColumns' => [
                    'status' => 'exact',
                    'type' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query) use ($store) {
                    $query->where('store_id', $store->id);
                },
            ]
        );
    }

    public function store(StoreReturnRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $order = Order::findOrFail($data['order_id']);

            $return = $this->returnService->createReturn(
                $order,
                $data['items'],
                ReturnType::from($data['type']),
                $data['return_reason_id'] ?? null,
                $data['reason'] ?? null,
                $data['notes'] ?? null,
                $data['refund_method'] ?? null,
                $data['restocking_fee'] ?? 0
            );

            return response()->json([
                'data' => new ReturnResource($return->load(['order', 'customer', 'items'])),
                'notifications' => [['type' => 'success', 'message' => 'Return created successfully']],
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $e->getMessage()]],
            ], 422);
        }
    }

    public function show(OrderReturn $return): JsonResponse
    {
        return response()->json([
            'data' => new ReturnResource($return->load(['order.items', 'customer', 'processedBy', 'items.product', 'items.variant', 'returnReason'])),
        ]);
    }

    public function getReturnableItems(Order $order): JsonResponse
    {
        if (!$order->canBeReturned()) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Order cannot be returned']],
            ], 422);
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
                'data' => new ReturnResource($return->load(['order', 'customer', 'items'])),
                'notifications' => [['type' => 'success', 'message' => 'Return approved successfully']],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $e->getMessage()]],
            ], 422);
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
                'data' => new ReturnResource($return->load(['order', 'customer', 'items'])),
                'notifications' => [['type' => 'warning', 'message' => 'Return rejected']],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $e->getMessage()]],
            ], 422);
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
                'data' => new ReturnResource($return->load(['order', 'customer', 'items'])),
                'notifications' => [['type' => 'success', 'message' => 'Return processed successfully']],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => $e->getMessage()]],
            ], 422);
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
