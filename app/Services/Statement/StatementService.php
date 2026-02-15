<?php

namespace App\Services\Statement;

use App\Models\Credit;
use App\Models\Customer;
use App\Models\Vendor;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StatementService
{
    /**
     * Generate customer account statement.
     */
    public function getCustomerStatement(
        Customer $customer,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null
    ): array {
        $fromDate = $fromDate ?? now()->subMonths(3)->startOfDay();
        $toDate = $toDate ?? now()->endOfDay();

        $transactions = $this->buildCustomerTransactions($customer, $fromDate, $toDate);
        $openingBalance = $this->getOpeningBalance($customer, $fromDate);
        $runningBalance = $openingBalance;

        $transactions = $transactions->map(function ($tx) use (&$runningBalance) {
            $runningBalance = $runningBalance + $tx['debit'] - $tx['credit'];
            $tx['balance'] = $runningBalance;
            $tx['date'] = $tx['date']->format('Y-m-d H:i');
            return $tx;
        });

        return [
            'entity_type' => 'customer',
            'entity' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'credit_limit' => (float) $customer->credit_limit,
                'current_balance' => (float) $customer->credit_balance,
            ],
            'period' => [
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'transactions' => $transactions->toArray(),
        ];
    }

    /**
     * Generate vendor account statement.
     */
    public function getVendorStatement(
        Vendor $vendor,
        ?Carbon $fromDate = null,
        ?Carbon $toDate = null
    ): array {
        $fromDate = $fromDate ?? now()->subMonths(3)->startOfDay();
        $toDate = $toDate ?? now()->endOfDay();

        $transactions = $this->buildVendorTransactions($vendor, $fromDate, $toDate);
        $openingBalance = $this->getVendorOpeningBalance($vendor, $fromDate);
        $runningBalance = $openingBalance;

        $transactions = $transactions->map(function ($tx) use (&$runningBalance) {
            $runningBalance = $runningBalance - $tx['debit'] + $tx['credit'];
            $tx['balance'] = $runningBalance;
            $tx['date'] = $tx['date']->format('Y-m-d H:i');
            return $tx;
        });

        return [
            'entity_type' => 'vendor',
            'entity' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'phone' => $vendor->phone,
                'current_balance' => (float) ($vendor->balance ?? 0),
            ],
            'period' => [
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
            ],
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'total_debit' => $transactions->sum('debit'),
            'total_credit' => $transactions->sum('credit'),
            'transactions' => $transactions->toArray(),
        ];
    }

    /**
     * Build customer transaction list from orders, payments and credits.
     */
    private function buildCustomerTransactions(Customer $customer, Carbon $from, Carbon $to): Collection
    {
        $transactions = collect();

        // Get orders with payments
        $orders = $customer->orders()
            ->with('payments')
            ->whereBetween('created_at', [$from, $to])
            ->get();

        foreach ($orders as $order) {
            $transactions->push([
                'date' => $order->created_at,
                'type' => 'order',
                'reference' => $order->order_number,
                'description' => "Order #{$order->order_number}",
                'debit' => (float) $order->total,
                'credit' => 0,
            ]);

            foreach ($order->payments as $payment) {
                $transactions->push([
                    'date' => $payment->created_at,
                    'type' => 'payment',
                    'reference' => $payment->reference_number ?? $order->order_number,
                    'description' => "Payment ({$payment->payment_method})",
                    'debit' => 0,
                    'credit' => (float) $payment->amount,
                ]);
            }
        }

        // Get credits (using polymorphic relation)
        $credits = $customer->credits()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        foreach ($credits as $credit) {
            $transactions->push([
                'date' => $credit->created_at,
                'type' => $credit->type,
                'reference' => $credit->reference ?? '-',
                'description' => $credit->notes ?? ($credit->isCredit() ? 'Credit Added' : 'Credit Used'),
                'debit' => $credit->isDebit() ? abs((float) $credit->amount) : 0,
                'credit' => $credit->isCredit() ? (float) $credit->amount : 0,
            ]);
        }

        return $transactions->sortBy('date')->values();
    }

    /**
     * Build vendor transaction list from purchase orders and credits.
     */
    private function buildVendorTransactions(Vendor $vendor, Carbon $from, Carbon $to): Collection
    {
        $transactions = collect();

        // Get purchase orders
        $purchaseOrders = $vendor->purchaseOrders()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        foreach ($purchaseOrders as $po) {
            $transactions->push([
                'date' => $po->created_at,
                'type' => 'purchase_order',
                'reference' => $po->po_number,
                'description' => "Purchase Order #{$po->po_number}",
                'debit' => 0,
                'credit' => (float) $po->total,
            ]);
        }

        // Get credits (using polymorphic relation)
        $credits = $vendor->credits()
            ->whereBetween('created_at', [$from, $to])
            ->get();

        foreach ($credits as $credit) {
            $transactions->push([
                'date' => $credit->created_at,
                'type' => $credit->type,
                'reference' => $credit->reference ?? '-',
                'description' => $credit->notes ?? ($credit->isCredit() ? 'Payment to Vendor' : 'Credit from Vendor'),
                'debit' => $credit->isDebit() ? abs((float) $credit->amount) : 0,
                'credit' => $credit->isCredit() ? (float) $credit->amount : 0,
            ]);
        }

        return $transactions->sortBy('date')->values();
    }

    /**
     * Get customer opening balance at a given date.
     */
    private function getOpeningBalance(Customer $customer, Carbon $date): float
    {
        $lastCredit = $customer->credits()
            ->where('created_at', '<', $date)
            ->orderByDesc('created_at')
            ->first();

        return $lastCredit ? (float) $lastCredit->balance_after : 0;
    }

    /**
     * Get vendor opening balance at a given date.
     */
    private function getVendorOpeningBalance(Vendor $vendor, Carbon $date): float
    {
        $lastCredit = $vendor->credits()
            ->where('created_at', '<', $date)
            ->orderByDesc('created_at')
            ->first();

        return $lastCredit ? (float) $lastCredit->balance_after : 0;
    }
}
