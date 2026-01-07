<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Vendor;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class DashboardController extends Controller
{

    private $service;

    public function __construct(InvoiceService $invoice)
    {
        $this->service = $invoice;
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
         * Margin / Profit Daily
         */
        $data = [];

        $queryResult = Order::whereMonth('date', date('m'))->whereYear('date',
            date('Y'))->orderByDesc('date')->get()->toArray();
        foreach ($queryResult as $order) {
            $date = $order['date'];
            $discount = $order['discount_type'] === 'rupee' ? $order['discount'] : ($order['discount'] * $order['sub_total'] / 100);
            $data[$date] = $data[$date] ?? [
                'date' => $date,
                'total' => 0,
                'delivery' => 0,
                'discount' => 0,
                'net' => 0,
            ];

            $data[$date]['total'] += $order['sub_total'];
            $data[$date]['delivery'] += $order['delivery_charges'];
            $data[$date]['discount'] += $discount;
            $data[$date]['net'] += $order['total'];
        }
        $data = array_values($data);

        /**
         * Margin / Profit Monthly with Daily Discount Calculation
         */
        $month = [];

// Fetch previous year's last 2 months + current year's months up to current month
        $currentYear = (int) date('Y');
        $previousYear = $currentYear - 1;
        $currentMonth = (int) date('m');

        $query = DB::select("
    SELECT
        MONTH(date) as month,
        MONTHNAME(date) as month_name,
        YEAR(date) as year,
        SUM(sub_total) as sub_total,
        SUM(total) as total
    FROM orders
    WHERE (YEAR(date) = ? AND MONTH(date) IN (11, 12))
       OR (YEAR(date) = ? AND MONTH(date) <= ?)
    GROUP BY YEAR(date), MONTH(date), MONTHNAME(date)
    ORDER BY YEAR(date), MONTH(date)
", [$previousYear, $currentYear, $currentMonth]);

        foreach ($query as $r) {
            $rowYear = $r->year;
            $monthName = Carbon::create()->month($r->month)->format('M') . ' ' . $rowYear;
            $subTotal = $r->sub_total; // This is the equivalent of the old profit calculation
            $netAmount = $r->total;    // This is the equivalent of the old net amount

            // Calculate daily discount for the month
            $dailyDiscount = 0;
            $dailyQuery = Order::whereMonth('date', $r->month)
                ->whereYear('date', $rowYear)
                ->selectRaw('date, SUM(CASE WHEN discount_type = "rupee" THEN discount ELSE (discount * sub_total / 100) END) as daily_discount')
                ->groupBy('date')
                ->get();

            $dailyDelivery = 0;
            $dailyDeliveryQuery = Order::whereMonth('date', $r->month)
                ->whereYear('date', $rowYear)
                ->selectRaw('date, SUM(delivery_charges) as daily_delivery')
                ->groupBy('date')
                ->get();

            foreach ($dailyDeliveryQuery as $daily) {
                $dailyDelivery += $daily->daily_delivery;
            }

            foreach ($dailyQuery as $daily) {
                $dailyDiscount += $daily->daily_discount;
            }

            // Fetch vendor discounts and expenses for the month
            $discountVendor = Stock::whereMonth('date', $r->month)
                ->whereYear('date', $rowYear)
                ->sum('discount');

            $discountVendor += Account::whereMonth('created_at', $r->month)
                ->whereYear('created_at', $rowYear)
                ->where('party_type', Vendor::class)
                ->sum('discount');

            $discountCustomer = Account::whereMonth('created_at', $r->month)
                ->whereYear('created_at', $rowYear)
                ->where('party_type', Customer::class)
                ->sum('discount');

            $expense = Account::whereMonth('created_at', $r->month)
                ->whereYear('created_at', $rowYear)
                ->where('party_type', Expense::class)
                ->where('party_id', '!=', 6)
                ->sum('debit');

            // Final result for the month
            $month[$monthName] = [
                'month' => $monthName,
                'daily_discount' => $dailyDiscount,
                'discount' => $discountCustomer,
                'expense' => $expense,
                'vendor_discount' => $discountVendor,
                'daily_delivery' => $dailyDelivery,
                'sub_total' => $subTotal + $dailyDelivery,
                'net' => $netAmount - ($discountCustomer + $discountVendor + $expense), // Adjust net after all deductions
            ];
        }

        $month = array_values($month);

        /**
         * Bank Details
         */
        $bank_details = [];
        $banks = Bank::all();
        if (!empty($banks)) {
            foreach ($banks as $bank) {
                $account = DB::table('accounts')
                    ->select(DB::raw('sum(credit) as credit, sum(debit) as debit'))
                    ->where('bank_id', $bank->id)->get();
                $credit = $account[0]->credit;
                $debit = $account[0]->debit;
                $bank_details[] = [
                    'name' => $bank->name,
                    'amount' => $credit - $debit
                ];
            }
        }
        $customerAmount = DB::table('accounts')
            ->select(DB::raw('sum(credit) as credit, sum(debit) as debit'))
            ->where('party_type', Customer::class)->get();
        $creditCustomer = $customerAmount[0]->credit;
        $debitCustomer = $customerAmount[0]->debit;
        $vendorAmount = DB::table('accounts')
            ->select(DB::raw('sum(credit) as credit, sum(debit) as debit'))
            ->where('party_type', Vendor::class)->get();
        $creditVendor = $vendorAmount[0]->credit;
        $debitVendor = $vendorAmount[0]->debit;
        $bank_details[] = [
            'name' => 'Customer',
            'amount' => $debitCustomer - $creditCustomer
        ];
        $bank_details[] = [
            'name' => 'Vendor',
            'amount' => $creditVendor - $debitVendor
        ];
        $productsAll = Product::withCount('inventory')->get();
        $stockAmount = 0;
        foreach ($productsAll as $prod) {
            $stockAmount += $prod->inventory_count * $prod->purchase_price;
        }
        $bank_details[] = [
            'name' => 'Stock',
            'amount' => $stockAmount
        ];
        $expenses = Expense::all();
        $expense_details = $expenses->map(function ($expense) {
            $debit = $expense->accounts->filter(function ($account) {
                return $account->created_at->format('m') == date('m') && $account->created_at->format('Y') == date('Y');
            })->sum('debit');

            return [
                'name' => $expense->name,
                'amount' => $debit,
            ];
        });

        return view('admin.dashboard.index',
            compact('users', 'customers', 'vendors', 'data', 'categories', 'products', 'month', 'bank_details',
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
