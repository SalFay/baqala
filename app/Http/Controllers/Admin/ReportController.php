<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Stock;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class ReportController extends Controller
{

    private $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function availableStock(Request $request, DataTables $dataTables)
    {

        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->availableStock($request, $dataTables);
        }
        return view('admin.reports.availableStock');

    }

    public function orders(Request $request, DataTables $dataTables)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->orders($request, $dataTables);
        }
        return view('admin.reports.orders');

    }

    public function ordersItem(Request $request)
    {
        // One date range (default: this month) instead of every order line ever (35k+ rows).
        $range = $request->input('date') ?: now()->startOfMonth()->toDateString() . ' to ' . now()->toDateString();
        $from = substr(startDate($range), 0, 10);
        $to = substr(endDate($range), 0, 10);

        $orders = Order::query()->without(['customer', 'items'])
            ->whereBetween('date', [$from, $to])
            ->get(['id', 'date', 'sub_total', 'delivery_charges', 'total'])
            ->keyBy('id');

        $items = OrderItem::where('order_type', Order::class)
            ->whereIn('order_id', $orders->keys())
            ->orderByDesc('order_id')
            ->get();

        // Share each order's discount across its lines by sale value, so line discounts add
        // up to the order's real discount (it used to repeat the whole discount of an
        // unrelated order - looked up by item id - on every line).
        $lineSales = $items->groupBy('order_id')->map(fn ($lines) => $lines->sum(fn ($l) => $l->stock * $l->sale_price));
        foreach ($items as $item) {
            $order = $orders[$item->order_id];
            $sale = $item->stock * $item->sale_price;
            $item->discount_share = $lineSales[$item->order_id] > 0
                ? round($order->discount_amount * $sale / $lineSales[$item->order_id], 2)
                : 0;
            $item->order_date = $order->date;
        }

        return view('admin.reports.ordersItem', ['orders' => $items, 'range' => $range]);
    }

    public function stock()
    {
        $stock = Stock::all();
        return view('admin.reports.stock', compact('stock'));

    }

    public function inventory(Request $request, DataTables $dataTables)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->inventoryLog($request, $dataTables);
        }
        return view('admin.reports.inventory');

    }

    public function productSold(Request $request)
    {
        // Get the date range from the request
        $dateRange = $request->input('date_range');
        $startDate = null;
        $endDate = null;

        if (!$dateRange) {
            // Default to today's date range
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } else {
            // Replace "to" with "-" for consistent parsing
            $dateRange = str_replace(' to ', ' - ', $dateRange);
            $dates = explode(' - ', $dateRange);
            if (count($dates) === 1) {
                // Only one date provided, use it as both start and end
                $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
                $endDate = $startDate->copy()->endOfDay();
            } else {
                // Both start and end dates are provided
                $startDate = \Carbon\Carbon::parse($dates[0])->startOfDay();
                $endDate = \Carbon\Carbon::parse($dates[1])->endOfDay();
            }
        }
        // Fetch order items within the date range and group by product
        $orderItems = OrderItem::whereHas('order', function ($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        })
            ->with('product') // Eager load the product relationship
            ->get()
            ->groupBy('product_id'); // Group order items by product ID

        // Calculate total quantity sold and total sales for each product
        $products = $orderItems->map(function ($items, $productId) {
            $product = $items->first()->product;

            if (!$product) {
                return null; // Skip if product data is missing
            }

            $quantitySold = $items->sum('stock');
            $totalSales = $items->sum(function ($item) {
                return $item->stock * ($item->sale_price ?? 0); // Default to 0 if sale_price is null
            });

            return [
                'name' => $product->name,
                'quantity_sold' => $quantitySold,
                'sale_price' => $items->first()->sale_price ?? 0, // Default to 0 if sale_price is null
                'total_sales' => $totalSales,
            ];
        })->filter(); // Remove null entries

        // Calculate totals for the footer
        $totalQuantitySold = $products->sum('quantity_sold');
        $totalSales = $products->sum('total_sales');

        return view('admin.reports.productSold', compact('products', 'totalQuantitySold', 'totalSales'));
    }

}
