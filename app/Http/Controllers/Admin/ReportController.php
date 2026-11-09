<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\OrderListService;
use App\Services\ProductService;
use App\Services\SalesReportService;
use App\Support\DateRange;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /**
     * Orders list. GET ?range=&cashier=&q=&show= renders the page with its totals;
     * the table posts the same filters back for its rows.
     */
    public function orders(Request $request, DataTables $dataTables, OrderListService $orders)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $orders->dataTable($request, $dataTables);
        }
        $filters = $orders->filters($request);

        return view('admin.reports.orders', [
            'f'        => $filters,
            'summary'  => $orders->summary($filters),
            'cashiers' => $orders->cashiers(),
            'show'     => OrderListService::SHOW,
            'presets'  => DateRange::presets(),
        ]);
    }

    /**
     * Margin / Profit: every order line with its share of the order discount, and profit
     * where the line has a real cost. GET ?range=&category=&q= (default: this month).
     */
    public function ordersItem(Request $request, DataTables $dataTables, SalesReportService $sales)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $sales->linesTable($request, $dataTables);
        }
        $filters = $sales->filters($request, 'month');

        return view('admin.reports.ordersItem', [
            'f'          => $filters,
            'summary'    => $sales->summary($filters),
            'categories' => $sales->categories(),
            'presets'    => DateRange::presets(),
        ]);
    }

    /**
     * Stock (purchase) invoices from vendors. GET ?range=&vendor= (default: this year).
     */
    public function stock(Request $request)
    {
        $range = DateRange::parse($request->input('range'), 'year');
        $vendor = (int) $request->input('vendor', 0);

        $invoices = DB::table('stocks AS s')
            ->leftJoin('vendors AS v', 'v.id', '=', 's.vendor_id')
            ->whereNull('s.deleted_at')
            ->whereBetween('s.date', $range->dates())
            ->when($vendor > 0, fn ($q) => $q->where('s.vendor_id', $vendor))
            ->select('s.id', 's.date', 's.created_at', 's.invoice_no', 's.sub_total', 's.discount', 's.delivery_charges',
                's.total', 's.credit', 's.debit', 'v.name AS vendor')
            // A stock invoice's lines are its "Available" inventory log rows.
            ->selectSub(DB::table('inventory_log AS i')->selectRaw('COALESCE(SUM(i.stock), 0)')
                ->whereColumn('i.order_id', 's.id')->where('i.order_type', \App\Models\Stock::class)
                ->where('i.status', 'Available')->whereNull('i.deleted_at'), 'items')
            ->orderByDesc('s.date')->orderByDesc('s.id')
            ->get();

        return view('admin.reports.stock', [
            'range'    => $range,
            'vendor'   => $vendor,
            'vendors'  => DB::table('vendors')->whereNull('deleted_at')->orderBy('name')->pluck('name', 'id')->all(),
            'invoices' => $invoices,
            'presets'  => DateRange::presets(),
            'summary'  => [
                'invoices' => $invoices->count(),
                'items'    => (int) $invoices->sum('items'),
                'total'    => (float) $invoices->sum('total'),
                'paid'     => (float) $invoices->sum('credit'),
                'unpaid'   => (float) $invoices->sum('debit'),
            ],
        ]);
    }

    public function inventory(Request $request, DataTables $dataTables)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->inventoryLog($request, $dataTables);
        }
        return view('admin.reports.inventory');

    }

    /**
     * Product Sold: one row per product with quantity, orders, sales, discount and share,
     * plus sales by category. GET ?range=&category=&q=&sort=sales|qty (default: today).
     */
    public function productSold(Request $request, SalesReportService $sales)
    {
        // The old form sent "date_range" as "Y-m-d - Y-m-d"; keep those links working.
        if (!$request->filled('range') && $request->filled('date_range')) {
            $request->merge(['range' => str_replace(' - ', ' to ', $request->input('date_range'))]);
        }
        $filters = $sales->filters($request, 'today');
        $sort = $request->input('sort') === 'qty' ? 'qty' : 'sales';

        return view('admin.reports.productSold', [
            'f'          => $filters,
            'sort'       => $sort,
            'summary'    => $sales->summary($filters),
            'products'   => $sales->byProduct($filters, $sort),
            'byCategory' => $sales->byCategory($filters),
            'categories' => $sales->categories(),
            'presets'    => DateRange::presets(),
        ]);
    }

}
