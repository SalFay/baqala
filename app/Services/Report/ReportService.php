<?php

namespace App\Services\Report;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreInventory;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSalesReport(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): array {
        $query = Order::query()
            ->where('status', OrderStatus::COMPLETED)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate));

        return [
            'total_orders' => $query->count(),
            'total_sales' => $query->sum('total'),
            'total_tax' => $query->sum('tax_amount'),
            'total_discount' => $query->sum('discount'),
            'average_order_value' => $query->avg('total') ?? 0,
        ];
    }

    public function getSalesByProduct(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null,
        int $limit = 10
    ): \Illuminate\Support\Collection {
        return OrderItem::query()
            ->select([
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('COUNT(DISTINCT order_id) as order_count'),
            ])
            ->whereHas('order', function ($q) use ($storeId, $fromDate, $toDate) {
                $q->where('status', OrderStatus::COMPLETED)
                    ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                    ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
                    ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate));
            })
            ->with('product:id,name,sku')
            ->groupBy('product_id')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    public function getSalesByCategory(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): \Illuminate\Support\Collection {
        return OrderItem::query()
            ->select([
                'products.category_id',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.line_total) as total_revenue'),
            ])
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('order', function ($q) use ($storeId, $fromDate, $toDate) {
                $q->where('status', OrderStatus::COMPLETED)
                    ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                    ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
                    ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate));
            })
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    public function getDailySales(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): \Illuminate\Support\Collection {
        return Order::query()
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_sales'),
                DB::raw('SUM(tax_amount) as total_tax'),
            ])
            ->where('status', OrderStatus::COMPLETED)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();
    }

    public function getInventoryReport(?int $storeId = null): array
    {
        $query = StoreInventory::query()
            ->with(['product', 'variant'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId));

        $inventories = $query->get();

        $totalValue = $inventories->sum(function ($inv) {
            $cost = $inv->variant?->cost ?? $inv->variant?->purchase_price
                ?? $inv->product?->purchase_price ?? 0;
            return $inv->quantity * $cost;
        });

        return [
            'total_products' => $inventories->count(),
            'total_quantity' => $inventories->sum('quantity'),
            'total_value' => $totalValue,
            'low_stock_count' => $inventories->filter(fn($inv) => $inv->isLowStock())->count(),
            'out_of_stock_count' => $inventories->where('quantity', '<=', 0)->count(),
        ];
    }

    public function getProfitLossReport(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): array {
        $orders = Order::query()
            ->with('items')
            ->where('status', OrderStatus::COMPLETED)
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
            ->get();

        $totalRevenue = $orders->sum('total');
        $totalCost = $orders->flatMap->items->sum(function ($item) {
            return $item->quantity * $item->cost_price;
        });
        $grossProfit = $totalRevenue - $totalCost;

        return [
            'total_revenue' => $totalRevenue,
            'total_cost' => $totalCost,
            'gross_profit' => $grossProfit,
            'gross_margin' => $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0,
            'total_orders' => $orders->count(),
        ];
    }

    public function getDashboardStats(?int $storeId = null): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $todaySales = $this->getSalesReport($today, $today, $storeId);
        $yesterdaySales = $this->getSalesReport($yesterday, $yesterday, $storeId);
        $monthSales = $this->getSalesReport($startOfMonth, $today, $storeId);
        $inventory = $this->getInventoryReport($storeId);

        $salesGrowth = $yesterdaySales['total_sales'] > 0
            ? (($todaySales['total_sales'] - $yesterdaySales['total_sales']) / $yesterdaySales['total_sales']) * 100
            : 0;

        return [
            'today' => $todaySales,
            'yesterday' => $yesterdaySales,
            'month' => $monthSales,
            'inventory' => $inventory,
            'sales_growth' => round($salesGrowth, 2),
        ];
    }
}
