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
use App\Services\DashboardService;
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

    /**
     * ?range=Y-m-d to Y-m-d (default: this month so far), ?compare=previous|last_year|none
     */
    public function index(Request $request, DashboardService $dashboard)
    {
        [$from, $to] = $this->range($request->input('range'));
        $compare = in_array($request->input('compare'), ['previous', 'last_year', 'none'], true)
            ? $request->input('compare') : 'previous';
        [$cFrom, $cTo] = $this->compareRange($from, $to, $compare);

        return view('admin.dashboard.index', $dashboard->build($from, $to, $cFrom, $cTo) + [
            'range'       => $from->toDateString() . ' to ' . $to->toDateString(),
            'compareMode' => $compare,
            'presets'     => $this->presets(),
        ]);
    }

    private function range(?string $input): array
    {
        try {
            $parts = array_map('trim', explode('to', (string) $input));
            $from = Carbon::parse($parts[0])->startOfDay();
            $to = Carbon::parse($parts[1] ?? $parts[0])->startOfDay();
        } catch (\Throwable $e) {
            $from = now()->startOfMonth();
            $to = now()->startOfDay();
        }
        if (!$input) {
            $from = now()->startOfMonth();
            $to = now()->startOfDay();
        }
        return $from->lte($to) ? [$from, $to] : [$to, $from];
    }

    /**
     * previous: a range starting on the 1st compares with the same days of the month
     * before (1-9 Nov vs 1-9 Oct); any other range with the same number of days just before.
     */
    private function compareRange(Carbon $from, Carbon $to, string $mode): array
    {
        if ($mode === 'none') {
            return [null, null];
        }
        if ($mode === 'last_year') {
            return [$from->copy()->subYearNoOverflow(), $to->copy()->subYearNoOverflow()];
        }
        if ($from->day === 1) {
            $cFrom = $from->copy()->subMonthNoOverflow();
            $months = $from->diffInMonths($to->copy()->addDay());
            if ($months >= 1 && $to->isLastOfMonth()) {
                // whole months: the same number of whole months before
                return [$from->copy()->subMonthsNoOverflow($months), $from->copy()->subDay()];
            }
            return [$cFrom, $cFrom->copy()->addDays($from->diffInDays($to))->min($cFrom->copy()->endOfMonth())];
        }
        $days = $from->diffInDays($to) + 1;
        return [$from->copy()->subDays($days), $from->copy()->subDay()];
    }

    private function presets(): array
    {
        $d = fn (Carbon $a, Carbon $b) => $a->toDateString() . ' to ' . $b->toDateString();
        $today = now()->startOfDay();
        return [
            'Today'      => $d($today, $today),
            'Yesterday'  => $d($today->copy()->subDay(), $today->copy()->subDay()),
            'Last 7 days' => $d($today->copy()->subDays(6), $today),
            'This month' => $d($today->copy()->startOfMonth(), $today),
            'Last month' => $d($today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()),
            'This year'  => $d($today->copy()->startOfYear(), $today),
        ];
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
