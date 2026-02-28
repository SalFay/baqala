<?php

namespace App\Services\Dashboard;

use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreInventory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get all dashboard data in optimized queries
     * Uses single queries with date conditions instead of multiple separate queries
     */
    public function getDashboardData(int $storeId, string $startDate, string $endDate): array
    {
        $cacheKey = "dashboard:{$storeId}:{$startDate}:{$endDate}";
        $cacheTtl = 60; // 1 minute cache for real-time feel

        return Cache::remember($cacheKey, $cacheTtl, function () use ($storeId, $startDate, $endDate) {
            return [
                'stats' => $this->getStats($storeId),
                'salesChart' => $this->getSalesChart($storeId, $startDate, $endDate),
                'topProducts' => $this->getTopProducts($storeId, $startDate, $endDate),
                'topCategories' => $this->getTopCategories($storeId, $startDate, $endDate),
                'recentOrders' => $this->getRecentOrders($storeId),
                'lowStock' => $this->getLowStock($storeId),
                'ordersByStatus' => $this->getOrdersByStatus($storeId, $startDate, $endDate),
                'paymentMethods' => $this->getPaymentMethodStats($storeId, $startDate, $endDate),
            ];
        });
    }

    /**
     * Get all key stats in a single optimized query
     */
    public function getStats(int $storeId): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $weekStart = now()->startOfWeek()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $lastMonthStart = now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = now()->subMonth()->endOfMonth()->toDateString();

        // Single query for all sales stats using conditional aggregates
        $salesStats = Order::query()
            ->where('store_id', $storeId)
            ->where('status', OrderStatus::COMPLETED)
            ->select([
                // Today's stats
                DB::raw("SUM(CASE WHEN DATE(created_at) = '{$today}' THEN total ELSE 0 END) as today_sales"),
                DB::raw("COUNT(CASE WHEN DATE(created_at) = '{$today}' THEN 1 END) as today_orders"),
                // Yesterday's stats (for growth calculation)
                DB::raw("SUM(CASE WHEN DATE(created_at) = '{$yesterday}' THEN total ELSE 0 END) as yesterday_sales"),
                // This week
                DB::raw("SUM(CASE WHEN DATE(created_at) >= '{$weekStart}' THEN total ELSE 0 END) as week_sales"),
                DB::raw("COUNT(CASE WHEN DATE(created_at) >= '{$weekStart}' THEN 1 END) as week_orders"),
                // This month
                DB::raw("SUM(CASE WHEN DATE(created_at) >= '{$monthStart}' THEN total ELSE 0 END) as month_sales"),
                DB::raw("COUNT(CASE WHEN DATE(created_at) >= '{$monthStart}' THEN 1 END) as month_orders"),
                // Last month (for comparison)
                DB::raw("SUM(CASE WHEN DATE(created_at) BETWEEN '{$lastMonthStart}' AND '{$lastMonthEnd}' THEN total ELSE 0 END) as last_month_sales"),
                // All time average
                DB::raw("AVG(total) as avg_order_value"),
            ])
            ->first();

        // Inventory stats in single query
        $inventoryStats = StoreInventory::query()
            ->where('store_id', $storeId)
            ->select([
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(quantity) as total_stock'),
                DB::raw('COUNT(CASE WHEN quantity <= low_stock_threshold AND quantity > 0 THEN 1 END) as low_stock_count'),
                DB::raw('COUNT(CASE WHEN quantity <= 0 THEN 1 END) as out_of_stock_count'),
            ])
            ->first();

        // Customer count
        $totalCustomers = Customer::count();

        // Calculate growth percentages
        $dailyGrowth = $this->calculateGrowth($salesStats->today_sales, $salesStats->yesterday_sales);
        $monthlyGrowth = $this->calculateGrowth($salesStats->month_sales, $salesStats->last_month_sales);

        return [
            'todaySales' => (float) ($salesStats->today_sales ?? 0),
            'todayOrders' => (int) ($salesStats->today_orders ?? 0),
            'weekSales' => (float) ($salesStats->week_sales ?? 0),
            'weekOrders' => (int) ($salesStats->week_orders ?? 0),
            'monthSales' => (float) ($salesStats->month_sales ?? 0),
            'monthOrders' => (int) ($salesStats->month_orders ?? 0),
            'avgOrderValue' => (float) ($salesStats->avg_order_value ?? 0),
            'totalCustomers' => $totalCustomers,
            'lowStockCount' => (int) ($inventoryStats->low_stock_count ?? 0),
            'outOfStockCount' => (int) ($inventoryStats->out_of_stock_count ?? 0),
            'totalProducts' => (int) ($inventoryStats->total_products ?? 0),
            'dailyGrowth' => $dailyGrowth,
            'monthlyGrowth' => $monthlyGrowth,
        ];
    }

    /**
     * Get daily sales chart data
     */
    public function getSalesChart(int $storeId, string $startDate, string $endDate): Collection
    {
        return Order::query()
            ->where('store_id', $storeId)
            ->where('status', OrderStatus::COMPLETED)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total) as sales'),
            ])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date' => $row->date,
                'orders' => (int) $row->orders,
                'sales' => (float) $row->sales,
            ]);
    }

    /**
     * Get top selling products
     */
    public function getTopProducts(int $storeId, string $startDate, string $endDate, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->select([
                'order_items.product_id',
                'products.name',
                'products.sku',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.line_total) as total_revenue'),
            ])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', OrderStatus::COMPLETED)
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$startDate, $endDate])
            ->groupBy('order_items.product_id', 'products.name', 'products.sku')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id' => $row->product_id,
                'name' => $row->name,
                'sku' => $row->sku,
                'total_qty' => (int) $row->total_qty,
                'total_revenue' => (float) $row->total_revenue,
            ]);
    }

    /**
     * Get top categories by sales
     */
    public function getTopCategories(int $storeId, string $startDate, string $endDate, int $limit = 5): Collection
    {
        return OrderItem::query()
            ->select([
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.line_total) as total_revenue'),
                DB::raw('SUM(order_items.quantity) as total_qty'),
            ])
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', OrderStatus::COMPLETED)
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$startDate, $endDate])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'id' => $row->id,
                'name' => $row->name,
                'total_revenue' => (float) $row->total_revenue,
                'total_qty' => (int) $row->total_qty,
            ]);
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $storeId, int $limit = 10): Collection
    {
        $statusColors = [
            'pending' => 'orange',
            'processing' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            'refunded' => 'purple',
        ];

        return Order::query()
            ->select(['id', 'order_number', 'customer_id', 'customer_name', 'total', 'status', 'created_at'])
            ->with(['customer:id,first_name,last_name'])
            ->where('store_id', $storeId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer?->full_name ?? $order->customer_name ?? 'Walk-in',
                'total' => (float) $order->total,
                'status' => [
                    'name' => ucfirst($order->status->value ?? $order->status ?? 'pending'),
                    'color' => $statusColors[$order->status->value ?? $order->status] ?? 'default',
                ],
                'created_at' => $order->created_at->toISOString(),
            ]);
    }

    /**
     * Get low stock products
     */
    public function getLowStock(int $storeId, int $limit = 10): Collection
    {
        return StoreInventory::query()
            ->select(['store_inventories.id', 'store_inventories.product_id', 'store_inventories.quantity', 'store_inventories.low_stock_threshold', 'products.name', 'products.sku'])
            ->join('products', 'store_inventories.product_id', '=', 'products.id')
            ->where('store_inventories.store_id', $storeId)
            ->whereColumn('store_inventories.quantity', '<=', 'store_inventories.low_stock_threshold')
            ->orderBy('store_inventories.quantity')
            ->limit($limit)
            ->get()
            ->map(fn($inv) => [
                'id' => $inv->id,
                'product_id' => $inv->product_id,
                'name' => $inv->name,
                'sku' => $inv->sku,
                'quantity' => (int) $inv->quantity,
                'threshold' => (int) ($inv->low_stock_threshold ?? 5),
            ]);
    }

    /**
     * Get orders grouped by status
     */
    public function getOrdersByStatus(int $storeId, string $startDate, string $endDate): Collection
    {
        $statusColors = [
            'pending' => 'orange',
            'processing' => 'blue',
            'completed' => 'green',
            'cancelled' => 'red',
            'refunded' => 'purple',
        ];

        return Order::query()
            ->select([
                'status',
                DB::raw('COUNT(*) as count'),
            ])
            ->where('store_id', $storeId)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->groupBy('status')
            ->get()
            ->map(fn($row) => [
                'status' => ucfirst($row->status->value ?? $row->status ?? 'Unknown'),
                'color' => $statusColors[$row->status->value ?? $row->status] ?? 'default',
                'count' => (int) $row->count,
            ]);
    }

    /**
     * Get payment method distribution
     */
    public function getPaymentMethodStats(int $storeId, string $startDate, string $endDate): Collection
    {
        return DB::table('payments')
            ->select([
                'payment_methods.name as method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(payments.amount) as total'),
            ])
            ->join('orders', 'payments.order_id', '=', 'orders.id')
            ->leftJoin('payment_methods', 'payments.payment_method_id', '=', 'payment_methods.id')
            ->where('orders.store_id', $storeId)
            ->where('orders.status', OrderStatus::COMPLETED)
            ->where('payments.status', 'completed')
            ->whereBetween(DB::raw('DATE(orders.created_at)'), [$startDate, $endDate])
            ->groupBy('payment_methods.id', 'payment_methods.name')
            ->get()
            ->map(fn($row) => [
                'method' => $row->method ?? 'Cash',
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ]);
    }

    /**
     * Calculate percentage growth
     */
    private function calculateGrowth(float $current, float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100 : 0;
        }
        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Clear dashboard cache
     */
    public function clearCache(int $storeId): void
    {
        $pattern = "dashboard:{$storeId}:*";
        // Clear cache for this store
        Cache::forget($pattern);
    }
}
