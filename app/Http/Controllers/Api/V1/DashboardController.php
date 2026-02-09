<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function stats(Request $request): JsonResponse
    {
        $stats = $this->reportService->getDashboardStats($request->store_id);

        return response()->json($stats);
    }

    public function salesChart(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $fromDate = $validated['from_date'] ?? now()->subDays(30)->toDateString();
        $toDate = $validated['to_date'] ?? now()->toDateString();

        $data = $this->reportService->getDailySales(
            $fromDate,
            $toDate,
            $validated['store_id'] ?? null
        );

        return response()->json($data);
    }

    public function topProducts(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $data = $this->reportService->getSalesByProduct(
            $validated['from_date'] ?? now()->startOfMonth()->toDateString(),
            $validated['to_date'] ?? now()->toDateString(),
            $validated['store_id'] ?? null,
            $validated['limit'] ?? 10
        );

        return response()->json($data);
    }

    public function lowStock(Request $request): JsonResponse
    {
        $storeId = $request->store_id ?? 1;
        $threshold = $request->threshold;

        $inventoryService = app(\App\Services\Inventory\InventoryService::class);
        $products = $inventoryService->getLowStockProducts($storeId, $threshold);

        return response()->json($products);
    }

    public function recentOrders(Request $request): JsonResponse
    {
        $orders = \App\Models\Order::query()
            ->with(['customer', 'user'])
            ->when($request->store_id, fn($q, $id) => $q->where('store_id', $id))
            ->orderByDesc('created_at')
            ->limit($request->limit ?? 10)
            ->get();

        return response()->json($orders);
    }
}
