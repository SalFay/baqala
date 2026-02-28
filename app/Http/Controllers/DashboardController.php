<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;
        $startDate = $request->start_date ?? now()->startOfMonth()->toDateString();
        $endDate = $request->end_date ?? now()->toDateString();

        // Get all dashboard data in optimized queries
        $data = $this->dashboardService->getDashboardData($storeId, $startDate, $endDate);

        return Inertia::render('Dashboard/Index', [
            'stats' => $data['stats'],
            'salesChart' => $data['salesChart'],
            'topProducts' => $data['topProducts'],
            'topCategories' => $data['topCategories'],
            'recentOrders' => $data['recentOrders'],
            'lowStock' => $data['lowStock'],
            'ordersByStatus' => $data['ordersByStatus'],
            'paymentMethods' => $data['paymentMethods'],
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }
}
