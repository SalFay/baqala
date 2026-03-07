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

    /**
     * Profit & Loss Report
     */
    public function profitLoss(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getProfitLossReport($fromDate, $toDate, $storeId)
            );
        }

        return Inertia::render('Reports/ProfitLoss', [
            'data' => $this->reportService->getProfitLossReport($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }

    /**
     * Daily Summary Report
     */
    public function dailySummary(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json([
                'daily' => $this->reportService->getDailySales($fromDate, $toDate, $storeId),
                'summary' => $this->reportService->getSalesReport($fromDate, $toDate, $storeId),
            ]);
        }

        return Inertia::render('Reports/DailySummary', [
            'daily' => $this->reportService->getDailySales($fromDate, $toDate, $storeId),
            'summary' => $this->reportService->getSalesReport($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }

    /**
     * Sales by Category Report
     */
    public function salesByCategory(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getSalesByCategory($fromDate, $toDate, $storeId)
            );
        }

        return Inertia::render('Reports/SalesByCategory', [
            'categories' => $this->reportService->getSalesByCategory($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }

    /**
     * Sales by Customer Report
     */
    public function salesByCustomer(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getSalesByCustomer($fromDate, $toDate, $storeId)
            );
        }

        return Inertia::render('Reports/SalesByCustomer', [
            'customers' => $this->reportService->getSalesByCustomer($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }

    /**
     * Payment Methods Report
     */
    public function paymentMethods(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getPaymentMethodReport($fromDate, $toDate, $storeId)
            );
        }

        return Inertia::render('Reports/PaymentMethodReport', [
            'payments' => $this->reportService->getPaymentMethodReport($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }

    /**
     * Stock Valuation Report
     */
    public function stockValuation(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $locationId = $request->location_id;

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getStockValuation($storeId, $locationId)
            );
        }

        return Inertia::render('Reports/StockValuation', [
            'data' => $this->reportService->getStockValuation($storeId, $locationId),
            'filters' => compact('storeId', 'locationId'),
        ]);
    }

    /**
     * Expiry Report
     */
    public function expiryReport(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $daysAhead = $request->days_ahead ?? 30;

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getExpiryReport($storeId, $daysAhead)
            );
        }

        return Inertia::render('Reports/ExpiryReport', [
            'items' => $this->reportService->getExpiryReport($storeId, $daysAhead),
            'filters' => compact('storeId', 'daysAhead'),
        ]);
    }

    /**
     * Customer Aging Report
     */
    public function customerAging(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getCustomerAging($storeId)
            );
        }

        return Inertia::render('Reports/CustomerAging', [
            'customers' => $this->reportService->getCustomerAging($storeId),
            'filters' => compact('storeId'),
        ]);
    }

    /**
     * Cash Register Report
     */
    public function cashRegisterReport(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();

        if ($request->wantsJson()) {
            return response()->json(
                $this->reportService->getCashRegisterReport($fromDate, $toDate, $storeId)
            );
        }

        return Inertia::render('Reports/CashRegisterReport', [
            'registers' => $this->reportService->getCashRegisterReport($fromDate, $toDate, $storeId),
            'filters' => compact('fromDate', 'toDate', 'storeId'),
        ]);
    }
}
