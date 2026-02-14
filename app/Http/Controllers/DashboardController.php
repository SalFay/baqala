<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Report\ReportService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): Response
    {
        $storeId = $request->store_id ?? auth()->user()->store_id ?? 1;

        $stats = $this->reportService->getDashboardStats($storeId);

        $recentOrders = Order::query()
            ->with(['customer', 'user', 'currentStatus'])
            ->where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->full_name ?? $order->customer_name ?? 'Walk-in',
                'total' => $order->total,
                'current_status' => $order->currentStatus ? [
                    'name' => $order->currentStatus->name,
                    'color' => $order->currentStatus->color,
                ] : null,
                'created_at' => $order->created_at,
            ]);

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}
