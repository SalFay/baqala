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

    public function __construct( InvoiceService $invoice )
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


        $queryResult = Order::whereMonth( 'date', date( 'm' ) )->whereYear( 'date',
            date( 'Y' ) )->orderByDesc( 'date' )->get()->toArray();
        foreach( $queryResult as $order ) {
            $discount = Order::where('date', $order['date'])->sum('discount');

            foreach( $order[ 'items' ] as $item ) {
                $date = $item[ 'date' ];
                $price = $item[ 'sale_price' ] - $item[ 'purchase_price' ];
                $profit = $price * $item[ 'stock' ];
                $data[ $date ] = $data[ $date ] ?? [
                    'date'     => $date,
                    'stock'    => 0,
                    'discount' => $discount,
                    'profit'   => 0,
                    'net'      => 0,
                ];
                $data[ $date ][ 'stock' ] += $item[ 'stock' ];
                $data[ $date ][ 'profit' ] += $profit;
                $data[ $date ][ 'net' ] += $profit;
            }
        }
        $data = array_values( $data );
        /**
         * Margin / Profit Monthly
         */
        $month = [];
        $query1 = \DB::select( "  SELECT
        MONTH(date) as month,
        MONTHNAME(date) as month_name,
        SUM(stock) as total_stock,
        SUM((sale_price - purchase_price) * stock) as total_price
    FROM order_items
    WHERE YEAR(date) = YEAR(CURDATE() - INTERVAL 1 MONTH)
    AND MONTH(date) = 12
    GROUP BY MONTH(date), MONTHNAME(date)" );
        foreach( $query1 as $r ) {
            $monthName = Carbon::create()->month( $r->month )->format( 'M' );
            $profit = $r->total_price;
            $discount = Order::whereMonth( 'date', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) - 1 )
                ->sum( 'discount' );
            $discountVendor = Stock::whereMonth( 'date', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) - 1)
                ->sum( 'discount' );
            $discountVendor += Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) - 1)
                ->where( 'party_type', Vendor::class )
                ->sum( 'discount' );
            $discount += Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) - 1)
                ->where( 'party_type', Customer::class )
                ->sum( 'discount' );
            $expense = Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) - 1)
                ->where( 'party_type', Expense::class )
                ->where( 'party_id', '!=', 6 )
                ->sum( 'debit' );

            $month[$monthName] = $month[$monthName] ?? [
                'month' => $monthName,
                'stock' => 0,
                'discount' => $discount,
                'expense' => $expense,
                'vendor_discount' => $discountVendor,
                'profit' => 0,
                'net' => 0,
            ];

            $month[$monthName]['stock'] += $r->total_stock;
            $month[$monthName]['profit'] += $profit;
            $month[$monthName]['net'] += $profit;

        }

        $query1 = \DB::select( "SELECT
    MONTH(date) as month,
    MONTHNAME(date) as month_name,
    SUM(stock) as total_stock,
    SUM((sale_price - purchase_price) * stock) as total_price
    FROM order_items
    WHERE YEAR(date) = YEAR(CURDATE())
    AND MONTH(date) IN (MONTH(CURDATE()), MONTH(CURDATE() - INTERVAL 1 MONTH))
    GROUP BY MONTH(date), MONTHNAME(date)" );
        foreach( $query1 as $r ) {
            $monthName = Carbon::create()->month( $r->month )->format( 'M' );
            $profit = $r->total_price;
            $discount = Order::whereMonth( 'date', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) )
                ->sum( 'discount' );
            $discountVendor = Stock::whereMonth( 'date', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) )
                ->sum( 'discount' );
            $discountVendor += Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) )
                ->where( 'party_type', Vendor::class )
                ->sum( 'discount' );
            $discount += Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) )
                ->where( 'party_type', Customer::class )
                ->sum( 'discount' );
            $expense = Account::whereMonth( 'created_at', $r->month )
                ->whereYear( 'created_at', date( 'Y' ) )
                ->where( 'party_type', Expense::class )
                ->where( 'party_id', '!=', 6 )
                ->sum( 'debit' );

            $month[$monthName] = $month[$monthName] ?? [
                'month' => $monthName,
                'stock' => 0,
                'discount' => $discount,
                'expense' => $expense,
                'vendor_discount' => $discountVendor,
                'profit' => 0,
                'net' => 0,
            ];

            $month[$monthName]['stock'] += $r->total_stock;
            $month[$monthName]['profit'] += $profit;
            $month[$monthName]['net'] += $profit;

        }

        $month = array_values( $month );
        /**
         * Bank Details
         */
        $bank_details = [];
        $banks = Bank::all();
        if( !empty( $banks ) ) {
            foreach( $banks as $bank ) {
                $account = DB::table( 'accounts' )
                    ->select( DB::raw( 'sum(credit) as credit, sum(debit) as debit' ) )
                    ->where( 'bank_id', $bank->id )->get();
                $credit = $account[ 0 ]->credit;
                $debit = $account[ 0 ]->debit;
                $bank_details[] = [
                    'name'   => $bank->name,
                    'amount' => $credit - $debit
                ];
            }
        }
        $customerAmount = DB::table( 'accounts' )
            ->select( DB::raw( 'sum(credit) as credit, sum(debit) as debit' ) )
            ->where( 'party_type', Customer::class )->get();
        $creditCustomer = $customerAmount[ 0 ]->credit;
        $debitCustomer = $customerAmount[ 0 ]->debit;
        $vendorAmount = DB::table( 'accounts' )
            ->select( DB::raw( 'sum(credit) as credit, sum(debit) as debit' ) )
            ->where( 'party_type', Vendor::class )->get();
        $creditVendor = $vendorAmount[ 0 ]->credit;
        $debitVendor = $vendorAmount[ 0 ]->debit;
        $bank_details[] = [
            'name'   => 'Customer',
            'amount' => $debitCustomer - $creditCustomer
        ];
        $bank_details[] = [
            'name'   => 'Vendor',
            'amount' => $creditVendor - $debitVendor
        ];
        $productsAll = Product::withCount( 'inventory' )->get();
        $stockAmount = 0;
        foreach( $productsAll as $prod ) {
            $stockAmount += $prod->inventory_count * $prod->purchase_price;
        }
        $bank_details[] = [
            'name'   => 'Stock',
            'amount' => $stockAmount
        ];
        $expenses = Expense::all();
        $expense_details = $expenses->map( function( $expense ) {
            $debit = $expense->accounts->filter( function( $account ) {
                return $account->created_at->format( 'm' ) == date( 'm' ) && $account->created_at->format( 'Y' ) == date( 'Y' );
            } )->sum( 'debit' );

            return [
                'name'   => $expense->name,
                'amount' => $debit,
            ];
        } );

        return view( 'admin.dashboard.index',
            compact( 'users', 'customers', 'vendors', 'data', 'categories', 'products', 'month', 'bank_details',
                'expense_details' ) );
    }

    public function customerInvoices( Request $request, DataTables $dataTables )
    {
        if( $request->ajax() && $request->isMethod( 'post' ) ) {
            return $this->service->customerDataTables( $request, $dataTables );
        }

        return view( 'admin.invoices.customer' );
    }

    public function vendorInvoices( Request $request, DataTables $dataTables )
    {
        if( $request->ajax() && $request->isMethod( 'post' ) ) {
            return $this->service->vendorDataTables( $request, $dataTables );
        }

        return view( 'admin.invoices.vendor' );
    }

}
