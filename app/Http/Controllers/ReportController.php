<?php

namespace App\Http\Controllers;

use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $salesData = $this->reportService->getDailySales($fromDate, $toDate, $storeId);
        $stats = $this->reportService->getDashboardStats($storeId);
        $topProducts = $this->reportService->getSalesByProduct($fromDate, $toDate, $storeId, 10);

        return Inertia::render('Reports/Index', [
            'salesData' => $salesData,
            'stats' => $stats,
            'topProducts' => $topProducts,
            'filters' => [
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'store_id' => $storeId,
            ],
        ]);
    }

    public function sales(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        $groupBy = $request->group_by ?? 'day';

        $salesData = $this->reportService->getSalesReport($fromDate, $toDate, $storeId, $groupBy);

        return Inertia::render('Reports/Sales', [
            'salesData' => $salesData,
            'filters' => compact('fromDate', 'toDate', 'storeId', 'groupBy'),
        ]);
    }

    public function inventory(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;

        // Get inventory items with product details
        $inventoryItems = \App\Models\StoreInventory::query()
            ->with(['product:id,name,sku,category_id,cost_price', 'product.category:id,name'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'name' => $inv->product?->name ?? 'Unknown',
                'sku' => $inv->product?->sku ?? '-',
                'category' => $inv->product?->category?->name ?? '-',
                'quantity' => $inv->quantity,
                'min_stock' => $inv->low_stock_threshold ?? 10,
                'max_stock' => 100,
                'cost' => $inv->product?->cost_price ?? 0,
            ]);

        return Inertia::render('Reports/Inventory', [
            'inventory' => $inventoryItems,
            'filters' => ['store_id' => $storeId],
        ]);
    }

    public function customers(Request $request): Response
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        $customerReport = $this->reportService->getCustomerReport($fromDate, $toDate);

        return Inertia::render('Reports/Customers', [
            'customers' => $customerReport,
            'filters' => compact('fromDate', 'toDate'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $type = $request->type ?? 'sales';
        $format = $request->format ?? 'csv';
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        return $this->reportService->exportReport($type, $format, $fromDate, $toDate);
    }
}
