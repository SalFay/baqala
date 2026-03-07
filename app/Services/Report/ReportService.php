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

    /**
     * Get sales by customer
     */
    public function getSalesByCustomer(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null,
        int $limit = 50
    ): \Illuminate\Support\Collection {
        return Order::query()
            ->select([
                'customer_id',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(total) as avg_order_value'),
                DB::raw('MAX(created_at) as last_order'),
            ])
            ->with('customer:id,name,email,phone')
            ->where('status', OrderStatus::COMPLETED)
            ->whereNotNull('customer_id')
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate))
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();
    }

    /**
     * Get payment method breakdown
     */
    public function getPaymentMethodReport(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): \Illuminate\Support\Collection {
        return \App\Models\Payment::query()
            ->select([
                'payment_method',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(amount) as total_amount'),
            ])
            ->whereHas('order', function ($q) use ($storeId, $fromDate, $toDate) {
                $q->where('status', OrderStatus::COMPLETED)
                    ->when($storeId, fn($q) => $q->where('store_id', $storeId))
                    ->when($fromDate, fn($q) => $q->whereDate('created_at', '>=', $fromDate))
                    ->when($toDate, fn($q) => $q->whereDate('created_at', '<=', $toDate));
            })
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();
    }

    /**
     * Get stock valuation report
     */
    public function getStockValuation(?int $storeId = null, ?int $locationId = null): array
    {
        $query = StoreInventory::query()
            ->with(['product:id,name,sku,cost_price,selling_price,category_id', 'product.category:id,name'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId));

        $inventories = $query->get();

        $items = $inventories->map(function ($inv) {
            $costPrice = $inv->product?->cost_price ?? 0;
            $sellingPrice = $inv->product?->selling_price ?? 0;
            $costValue = $inv->quantity * $costPrice;
            $retailValue = $inv->quantity * $sellingPrice;

            return [
                'id' => $inv->id,
                'product_id' => $inv->product_id,
                'name' => $inv->product?->name ?? 'Unknown',
                'sku' => $inv->product?->sku ?? '-',
                'category' => $inv->product?->category?->name ?? 'Uncategorized',
                'quantity' => $inv->quantity,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'cost_value' => $costValue,
                'retail_value' => $retailValue,
                'potential_profit' => $retailValue - $costValue,
            ];
        });

        return [
            'items' => $items,
            'summary' => [
                'total_items' => $items->count(),
                'total_quantity' => $items->sum('quantity'),
                'total_cost_value' => $items->sum('cost_value'),
                'total_retail_value' => $items->sum('retail_value'),
                'potential_profit' => $items->sum('potential_profit'),
            ],
        ];
    }

    /**
     * Get expiring products report
     */
    public function getExpiryReport(?int $storeId = null, int $daysAhead = 30): \Illuminate\Support\Collection
    {
        return \App\Models\ProductBatch::query()
            ->with(['product:id,name,sku', 'storeInventory:id,store_id,quantity'])
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<=', now()->addDays($daysAhead))
            ->where('expiry_date', '>=', now())
            ->when($storeId, function ($q) use ($storeId) {
                $q->whereHas('storeInventory', fn($q) => $q->where('store_id', $storeId));
            })
            ->orderBy('expiry_date')
            ->get()
            ->map(fn($batch) => [
                'id' => $batch->id,
                'product_name' => $batch->product?->name ?? 'Unknown',
                'sku' => $batch->product?->sku ?? '-',
                'batch_number' => $batch->batch_number,
                'quantity' => $batch->quantity,
                'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                'days_until_expiry' => $batch->expiry_date?->diffInDays(now()),
                'is_expired' => $batch->expiry_date?->isPast(),
            ]);
    }

    /**
     * Get customer aging report (credit aging)
     */
    public function getCustomerAging(?int $storeId = null): \Illuminate\Support\Collection
    {
        return \App\Models\Customer::query()
            ->select([
                'customers.id',
                'customers.name',
                'customers.phone',
                'customers.email',
                'customers.credit_limit',
                'customers.current_balance',
            ])
            ->where('customers.current_balance', '>', 0)
            ->get()
            ->map(function ($customer) {
                // Get unpaid orders grouped by age
                $unpaidOrders = Order::query()
                    ->where('customer_id', $customer->id)
                    ->where('payment_status', '!=', 'paid')
                    ->get();

                $current = $unpaidOrders->filter(fn($o) => $o->created_at->diffInDays(now()) <= 30)->sum('total');
                $days30 = $unpaidOrders->filter(fn($o) => $o->created_at->diffInDays(now()) > 30 && $o->created_at->diffInDays(now()) <= 60)->sum('total');
                $days60 = $unpaidOrders->filter(fn($o) => $o->created_at->diffInDays(now()) > 60 && $o->created_at->diffInDays(now()) <= 90)->sum('total');
                $days90 = $unpaidOrders->filter(fn($o) => $o->created_at->diffInDays(now()) > 90)->sum('total');

                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'credit_limit' => $customer->credit_limit,
                    'current_balance' => $customer->current_balance,
                    'current' => $current,
                    '1_30_days' => $days30,
                    '31_60_days' => $days60,
                    '61_90_days' => $days60,
                    'over_90_days' => $days90,
                    'total_due' => $current + $days30 + $days60 + $days90,
                ];
            })
            ->filter(fn($c) => $c['total_due'] > 0);
    }

    /**
     * Get cash register sessions report
     */
    public function getCashRegisterReport(
        ?string $fromDate = null,
        ?string $toDate = null,
        ?int $storeId = null
    ): \Illuminate\Support\Collection {
        return \App\Models\CashRegister::query()
            ->with(['user:id,name', 'location:id,name'])
            ->when($storeId, fn($q) => $q->where('store_id', $storeId))
            ->when($fromDate, fn($q) => $q->whereDate('opened_at', '>=', $fromDate))
            ->when($toDate, fn($q) => $q->whereDate('opened_at', '<=', $toDate))
            ->orderByDesc('opened_at')
            ->get()
            ->map(fn($reg) => [
                'id' => $reg->id,
                'user' => $reg->user?->name ?? 'Unknown',
                'location' => $reg->location?->name ?? '-',
                'status' => $reg->status,
                'opened_at' => $reg->opened_at?->format('Y-m-d H:i'),
                'closed_at' => $reg->closed_at?->format('Y-m-d H:i'),
                'opening_cash' => $reg->opening_cash,
                'closing_cash' => $reg->closing_cash,
                'expected_cash' => $reg->expected_cash,
                'difference' => $reg->closing_cash - $reg->expected_cash,
                'total_sales' => $reg->total_sales,
                'pay_ins' => $reg->pay_ins,
                'pay_outs' => $reg->pay_outs,
            ]);
    }

    /**
     * Export report to CSV/Excel
     */
    public function exportReport(
        string $type,
        string $format,
        ?string $fromDate,
        ?string $toDate,
        ?int $storeId = null
    ): \Symfony\Component\HttpFoundation\StreamedResponse {
        $data = match ($type) {
            'sales' => $this->getDailySales($fromDate, $toDate, $storeId),
            'products' => $this->getSalesByProduct($fromDate, $toDate, $storeId, 1000),
            'categories' => $this->getSalesByCategory($fromDate, $toDate, $storeId),
            'customers' => $this->getSalesByCustomer($fromDate, $toDate, $storeId, 1000),
            'payments' => $this->getPaymentMethodReport($fromDate, $toDate, $storeId),
            default => collect([]),
        };

        $filename = "{$type}_report_{$fromDate}_{$toDate}.csv";

        return response()->streamDownload(function () use ($data) {
            $handle = fopen('php://output', 'w');

            // Header row
            if ($data->isNotEmpty()) {
                fputcsv($handle, array_keys($data->first()->toArray()));
            }

            // Data rows
            foreach ($data as $row) {
                fputcsv($handle, $row->toArray());
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Get customer report for Customers page
     */
    public function getCustomerReport(
        ?string $fromDate = null,
        ?string $toDate = null
    ): \Illuminate\Support\Collection {
        return $this->getSalesByCustomer($fromDate, $toDate, null, 100);
    }
}
