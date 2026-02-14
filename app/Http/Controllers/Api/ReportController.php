<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function sales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $data = $this->reportService->getSalesReport(
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null,
            $validated['store_id'] ?? null
        );

        return response()->json($data);
    }

    public function salesByProduct(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $data = $this->reportService->getSalesByProduct(
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null,
            $validated['store_id'] ?? null,
            $validated['limit'] ?? 20
        );

        return response()->json($data);
    }

    public function salesByCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $data = $this->reportService->getSalesByCategory(
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null,
            $validated['store_id'] ?? null
        );

        return response()->json($data);
    }

    public function inventory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $data = $this->reportService->getInventoryReport($validated['store_id'] ?? null);

        return response()->json($data);
    }

    public function profitLoss(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $data = $this->reportService->getProfitLossReport(
            $validated['from_date'] ?? null,
            $validated['to_date'] ?? null,
            $validated['store_id'] ?? null
        );

        return response()->json($data);
    }

    public function dailySales(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date',
            'store_id' => 'nullable|exists:stores,id',
        ]);

        $data = $this->reportService->getDailySales(
            $validated['from_date'] ?? now()->subDays(30)->toDateString(),
            $validated['to_date'] ?? now()->toDateString(),
            $validated['store_id'] ?? null
        );

        return response()->json($data);
    }
}
