<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vendor;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{
    private $service;
    private $year;

    public function __construct(InvoiceService $invoice)
    {
        $this->service = $invoice;
        $this->year = date('Y');
    }

    public function index()
    {
        /**
         * Pie Chart
         */
        $users = User::count();
        $customers = Customer::count();
        $vendors = Vendor::count();
        $categories = Category::count();
        $products = Product::count();

        /**
         * Margin / Profit Monthly
         */

        $query1 = DB::table('order_items')
            ->selectRaw('id, stock, sale_price - purchase_price as price, MONTH(date) as date, MONTHNAME(date) as month')
            ->whereYear('date', $this->year)
            ->orderBy('date', 'asc')
            ->get();

        $discounts = DB::table('orders')
            ->selectRaw('MONTH(date) as date, SUM(discount) as discount')
            ->whereYear('date', $this->year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('discount', 'date');

        $vendorDiscounts = DB::table('stocks')
            ->selectRaw('MONTH(date) as date, SUM(discount) as discount')
            ->whereYear('date', $this->year)
            ->groupBy(DB::raw('MONTH(date)'))
            ->pluck('discount', 'date');

        $vendorAccountDiscounts = DB::table('accounts')
            ->selectRaw('MONTH(created_at) as date, SUM(discount) as discount')
            ->whereYear('created_at', $this->year)
            ->where('party_type', Vendor::class)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('discount', 'date');

        $customerAccountDiscounts = DB::table('accounts')
            ->selectRaw('MONTH(created_at) as date, SUM(discount) as discount')
            ->whereYear('created_at', $this->year)
            ->where('party_type', Customer::class)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->pluck('discount', 'date');

        $month = [];

        foreach ($query1 as $r) {
            $profit = $r->price * $r->stock;
            $discount = $discounts[$r->date] ?? 0;
            $vendorDiscount = ($vendorDiscounts[$r->date] ?? 0) + ($vendorAccountDiscounts[$r->date] ?? 0);
            $discount += $customerAccountDiscounts[$r->date] ?? 0;

            if (isset($month[$r->month])) {
                $month[$r->month]['stock'] += $r->stock;
                $month[$r->month]['discount'] = $discount;
                $month[$r->month]['profit'] += $profit;
                $month[$r->month]['net'] += $profit;
                $month[$r->month]['vendor_discount'] = $vendorDiscount;
            } else {
                $month[$r->month] = [
                    'month' => $r->month,
                    'stock' => $r->stock,
                    'discount' => $discount,
                    'vendor_discount' => $vendorDiscount,
                    'profit' => $profit,
                    'net' => $profit
                ];
            }
        }

        /**
         * Bank Details
         */

        $bank_details = [];
        $expense_details = [];

// Get banks and their balances in a single query
        $banks = Bank::select('id', 'name')
            ->addSelect([
                'amount' => DB::table('accounts')
                    ->selectRaw('sum(credit) - sum(debit)')
                    ->whereColumn('accounts.bank_id', 'banks.id')
                    ->whereMonth('accounts.created_at', date('m'))
            ])
            ->get();

        foreach ($banks as $bank) {
            $bank_details[] = [
                'name' => $bank->name,
                'amount' => $bank->amount,
            ];
        }

// Get cash balance
        $cash = DB::table('accounts')
            ->selectRaw('sum(credit) as amount')
            ->where('bank_id', 0)
            ->whereMonth('created_at', date('m'))
            ->first();

        $bank_details[] = [
            'name' => 'Cash',
            'amount' => $cash->amount,
        ];

// Get customer and vendor balances
        $parties = DB::table('accounts')
            ->select('party_type', DB::raw('sum(credit) as credit, sum(debit) as debit'))
            ->whereIn('party_type', [Customer::class, Vendor::class])
            ->whereMonth('created_at', date('m'))
            ->groupBy('party_type')
            ->get();

        foreach ($parties as $party) {
            $bank_details[] = [
                'name' => $party->party_type == Customer::class ? 'Customer' : 'Vendor',
                'amount' => $party->party_type == Customer::class ? $party->debit - $party->credit : $party->credit - $party->debit,
            ];
        }

        // Get stock amount
        $stockAmount = DB::table('products')
            ->join(DB::raw('(SELECT product_id, COUNT(*) as num_rows FROM inventory WHERE status = "Available" GROUP BY product_id) as inventory_count'), 'products.id', '=', 'inventory_count.product_id')
            ->selectRaw('sum(products.purchase_price * inventory_count.num_rows) as total_stock_amount')
            ->first()->total_stock_amount;

        $bank_details[] = [
            'name' => 'Stock',
            'amount' => $stockAmount,
        ];

// Get expenses in a single query
        $expenses = Expense::select('id', 'name')
            ->addSelect([
                'amount' => DB::table('accounts')
                    ->selectRaw('sum(debit)')
                    ->where('party_type', 'App\Models\Expense')
                    ->whereColumn('accounts.party_id', 'expenses.id')
                    ->whereMonth('accounts.created_at', date('m'))
            ])
            ->get();

        foreach ($expenses as $expense) {
            $expense_details[] = [
                'name' => $expense->name,
                'amount' => $expense->amount,
            ];
        }


        return view('admin.dashboard.index',
            compact('users', 'customers', 'vendors', 'categories', 'products', 'month', 'bank_details',
                'expense_details'));
    }

    public function customerInvoices(Request $request, DataTables $dataTables)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->customerDataTables($request, $dataTables);
        }

        return view('admin.invoices.customer');
    }

    public function vendorInvoices(Request $request, DataTables $dataTables)
    {
        if ($request->ajax() && $request->isMethod('post')) {
            return $this->service->vendorDataTables($request, $dataTables);
        }
        return view('admin.invoices.vendor');
    }
}
