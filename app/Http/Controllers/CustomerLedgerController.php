<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Services\CreditService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerLedgerController extends Controller
{
    public function __construct(
        protected CreditService $creditService
    ) {}

    /**
     * Display ledger for a specific customer
     */
    public function show(Request $request, Customer $customer): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request, $customer);
        }

        return Inertia::render('Customers/Ledger', [
            'customer' => $customer->load('customerGroup'),
        ]);
    }

    /**
     * Get ledger entries for a customer
     */
    public function listing(Request $request, Customer $customer): JsonResponse
    {
        $query = CustomerLedger::forCustomer($customer->id)
            ->with('createdBy');

        if ($request->search) {
            $query->where('description', 'like', "%{$request->search}%");
        }

        if ($request->type) {
            $query->ofType($request->type);
        }

        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $entries = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($entry) => [
                'id' => $entry->id,
                'transaction_type' => $entry->transaction_type,
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance_after' => $entry->balance_after,
                'description' => $entry->description,
                'notes' => $entry->notes,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'created_by' => $entry->createdBy?->name,
                'created_at' => $entry->created_at->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'data' => $entries,
            'total' => $total,
            'summary' => [
                'current_balance' => $customer->current_balance,
                'credit_limit' => $customer->credit_limit,
                'available_credit' => $this->creditService->getAvailableCredit($customer),
            ],
        ]);
    }

    /**
     * Get customer statement
     */
    public function statement(Request $request, Customer $customer): JsonResponse
    {
        $statement = $this->creditService->getStatement(
            $customer,
            $request->from_date,
            $request->to_date
        );

        return response()->json($statement);
    }

    /**
     * Get aging report for a customer
     */
    public function aging(Customer $customer): JsonResponse
    {
        $aging = $this->creditService->getAgingReport($customer);

        return response()->json($aging);
    }

    /**
     * Record a payment from customer
     */
    public function collectPayment(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $entry = $this->creditService->recordPayment(
            $customer,
            $validated['amount'],
            null,
            $validated['notes'] ?? 'Payment received'
        );

        return response()->json([
            'data' => $entry,
            'new_balance' => $customer->fresh()->current_balance,
            'notifications' => [['type' => 'success', 'message' => 'Payment recorded successfully']],
        ]);
    }

    /**
     * Adjust customer balance
     */
    public function adjust(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:debit,credit',
            'reason' => 'required|string|max:500',
        ]);

        $entry = $this->creditService->adjustBalance(
            $customer,
            $validated['amount'],
            $validated['type'],
            $validated['reason']
        );

        return response()->json([
            'data' => $entry,
            'new_balance' => $customer->fresh()->current_balance,
            'notifications' => [['type' => 'success', 'message' => 'Balance adjusted successfully']],
        ]);
    }

    /**
     * Get customers with outstanding balances
     */
    public function outstanding(Request $request): JsonResponse
    {
        $minBalance = $request->min_balance ?? null;
        $customers = $this->creditService->getCustomersWithBalances($minBalance);

        return response()->json([
            'data' => $customers->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'current_balance' => $c->current_balance,
                'credit_limit' => $c->credit_limit,
            ]),
            'total_outstanding' => $customers->sum('current_balance'),
        ]);
    }

    /**
     * Recalculate customer balance
     */
    public function recalculate(Customer $customer): JsonResponse
    {
        $newBalance = $this->creditService->recalculateBalance($customer);

        return response()->json([
            'new_balance' => $newBalance,
            'notifications' => [['type' => 'success', 'message' => 'Balance recalculated']],
        ]);
    }
}
