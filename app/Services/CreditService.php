<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Collection;

class CreditService
{
    /**
     * Check if customer has enough credit limit for an amount
     */
    public function canExtendCredit(Customer $customer, float $amount): bool
    {
        if ($customer->credit_limit === null) {
            // No credit limit set = unlimited credit
            return true;
        }

        $availableCredit = $this->getAvailableCredit($customer);
        return $availableCredit >= $amount;
    }

    /**
     * Get available credit for a customer
     */
    public function getAvailableCredit(Customer $customer): float
    {
        if ($customer->credit_limit === null) {
            return PHP_FLOAT_MAX;
        }

        return max(0, $customer->credit_limit - $customer->current_balance);
    }

    /**
     * Record a credit sale
     */
    public function recordCreditSale(Customer $customer, float $amount, Order $order): CustomerLedger
    {
        return CustomerLedger::recordSale(
            $customer,
            $amount,
            $order,
            "Credit sale - Order #{$order->order_number}"
        );
    }

    /**
     * Record a payment received
     */
    public function recordPayment(Customer $customer, float $amount, ?Payment $payment = null, ?string $description = null): CustomerLedger
    {
        return CustomerLedger::recordPayment(
            $customer,
            $amount,
            $payment,
            $description ?? 'Payment received'
        );
    }

    /**
     * Record a refund
     */
    public function recordRefund(Customer $customer, float $amount, $reference = null, ?string $description = null): CustomerLedger
    {
        return CustomerLedger::recordRefund(
            $customer,
            $amount,
            $reference,
            $description ?? 'Refund issued'
        );
    }

    /**
     * Adjust customer balance
     */
    public function adjustBalance(Customer $customer, float $amount, string $type = 'debit', ?string $reason = null): CustomerLedger
    {
        return CustomerLedger::recordAdjustment(
            $customer,
            $amount,
            $type,
            $reason ?? 'Balance adjustment'
        );
    }

    /**
     * Get customer ledger entries
     */
    public function getLedgerEntries(Customer $customer, array $filters = []): Collection
    {
        $query = CustomerLedger::forCustomer($customer->id)
            ->orderBy('created_at', 'desc');

        if (isset($filters['type'])) {
            $query->ofType($filters['type']);
        }

        if (isset($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }

        if (isset($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }

        return $query->get();
    }

    /**
     * Get account statement for a customer
     */
    public function getStatement(Customer $customer, ?string $fromDate = null, ?string $toDate = null): array
    {
        $query = CustomerLedger::forCustomer($customer->id)
            ->orderBy('created_at', 'asc');

        if ($fromDate) {
            $query->where('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->where('created_at', '<=', $toDate);
        }

        $entries = $query->get();

        // Calculate opening balance
        $openingBalance = 0;
        if ($fromDate) {
            $openingBalance = CustomerLedger::forCustomer($customer->id)
                ->where('created_at', '<', $fromDate)
                ->sum('debit') - CustomerLedger::forCustomer($customer->id)
                ->where('created_at', '<', $fromDate)
                ->sum('credit');
        }

        $runningBalance = $openingBalance;
        $statementLines = [];

        foreach ($entries as $entry) {
            $runningBalance = $entry->balance_after;
            $statementLines[] = [
                'date' => $entry->created_at,
                'description' => $entry->description,
                'reference_type' => $entry->reference_type,
                'reference_id' => $entry->reference_id,
                'debit' => $entry->debit,
                'credit' => $entry->credit,
                'balance' => $runningBalance,
            ];
        }

        return [
            'customer' => $customer,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_debit' => $entries->sum('debit'),
            'total_credit' => $entries->sum('credit'),
            'entries' => $statementLines,
        ];
    }

    /**
     * Get customers with outstanding balances
     */
    public function getCustomersWithBalances(?float $minBalance = null): Collection
    {
        $query = Customer::where('current_balance', '>', 0);

        if ($minBalance !== null) {
            $query->where('current_balance', '>=', $minBalance);
        }

        return $query->orderBy('current_balance', 'desc')->get();
    }

    /**
     * Get customers who are over their credit limit
     */
    public function getOverLimitCustomers(): Collection
    {
        return Customer::whereNotNull('credit_limit')
            ->whereColumn('current_balance', '>', 'credit_limit')
            ->orderByRaw('current_balance - credit_limit DESC')
            ->get();
    }

    /**
     * Get aging report for a customer
     */
    public function getAgingReport(Customer $customer): array
    {
        $today = now();

        // Get unpaid invoices/entries grouped by age
        $entries = CustomerLedger::forCustomer($customer->id)
            ->where('debit', '>', 0)
            ->orderBy('created_at')
            ->get();

        $aging = [
            'current' => 0,      // 0-30 days
            '31_60' => 0,        // 31-60 days
            '61_90' => 0,        // 61-90 days
            'over_90' => 0,      // 90+ days
        ];

        foreach ($entries as $entry) {
            $daysPast = $entry->created_at->diffInDays($today);
            $amount = $entry->debit - $entry->credit;

            if ($amount <= 0) continue;

            if ($daysPast <= 30) {
                $aging['current'] += $amount;
            } elseif ($daysPast <= 60) {
                $aging['31_60'] += $amount;
            } elseif ($daysPast <= 90) {
                $aging['61_90'] += $amount;
            } else {
                $aging['over_90'] += $amount;
            }
        }

        $aging['total'] = array_sum($aging);

        return $aging;
    }

    /**
     * Recalculate customer balance from ledger entries
     */
    public function recalculateBalance(Customer $customer): float
    {
        $totalDebit = CustomerLedger::forCustomer($customer->id)->sum('debit');
        $totalCredit = CustomerLedger::forCustomer($customer->id)->sum('credit');

        $balance = $totalDebit - $totalCredit;

        $customer->update(['current_balance' => $balance]);

        return $balance;
    }
}
