<?php

namespace App\Services;

use App\Models\CashRegister;
use App\Models\CashRegisterTransaction;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Collection;

class CashRegisterService
{
    /**
     * Open a cash register
     */
    public function openRegister(CashRegister $register, float $openingCash, ?array $denominations = null, ?string $note = null): CashRegister
    {
        $register->open($openingCash, $denominations, $note);
        return $register->fresh();
    }

    /**
     * Close a cash register
     */
    public function closeRegister(CashRegister $register, float $closingCash, ?array $denominations = null, ?string $note = null): CashRegister
    {
        $register->close($closingCash, $denominations, $note);
        return $register->fresh();
    }

    /**
     * Get the current open register for the authenticated user
     */
    public function getCurrentRegister(): ?CashRegister
    {
        return CashRegister::getOpenRegisterForUser(auth()->id());
    }

    /**
     * Check if user has an open register
     */
    public function hasOpenRegister(): bool
    {
        return $this->getCurrentRegister() !== null;
    }

    /**
     * Record a sale in the register
     */
    public function recordSale(CashRegister $register, array $payments, $reference = null): Collection
    {
        $transactions = collect();

        foreach ($payments as $payment) {
            $transaction = $register->recordSale(
                $payment['amount'],
                $payment['payment_method_id'] ?? null,
                $reference
            );
            $transactions->push($transaction);
        }

        return $transactions;
    }

    /**
     * Record a refund in the register
     */
    public function recordRefund(CashRegister $register, array $payments, $reference = null): Collection
    {
        $transactions = collect();

        foreach ($payments as $payment) {
            $transaction = $register->recordRefund(
                $payment['amount'],
                $payment['payment_method_id'] ?? null,
                $reference
            );
            $transactions->push($transaction);
        }

        return $transactions;
    }

    /**
     * Add cash to register (pay in)
     */
    public function payIn(CashRegister $register, float $amount, ?string $note = null): CashRegisterTransaction
    {
        return $register->payIn($amount, $note);
    }

    /**
     * Remove cash from register (pay out)
     */
    public function payOut(CashRegister $register, float $amount, ?string $note = null): CashRegisterTransaction
    {
        return $register->payOut($amount, $note);
    }

    /**
     * Get register summary for a specific register
     */
    public function getRegisterSummary(CashRegister $register): array
    {
        $transactionSummary = $register->getTransactionSummary();

        $totalSales = 0;
        $totalRefunds = 0;

        foreach ($transactionSummary as $key => $value) {
            if (is_array($value)) {
                $totalSales += $value['sales'] ?? 0;
                $totalRefunds += $value['refunds'] ?? 0;
            }
        }

        return [
            'register' => $register,
            'opening_cash' => $register->opening_cash,
            'expected_cash' => $register->calculateExpectedCash(),
            'total_sales' => $totalSales,
            'total_refunds' => $totalRefunds,
            'net_sales' => $totalSales - $totalRefunds,
            'pay_ins' => $transactionSummary['pay_in'] ?? 0,
            'pay_outs' => $transactionSummary['pay_out'] ?? 0,
            'transactions_count' => $register->transactions()->count(),
            'by_payment_method' => collect($transactionSummary)->filter(fn($v) => is_array($v)),
        ];
    }

    /**
     * Get all transactions for a register
     */
    public function getTransactions(CashRegister $register, array $filters = []): Collection
    {
        $query = $register->transactions()
            ->with(['paymentMethod', 'createdBy'])
            ->orderBy('created_at', 'desc');

        if (isset($filters['type'])) {
            $query->where('transaction_type', $filters['type']);
        }

        if (isset($filters['payment_method_id'])) {
            $query->where('payment_method_id', $filters['payment_method_id']);
        }

        return $query->get();
    }

    /**
     * Get daily report for all registers
     */
    public function getDailyReport(?string $date = null): array
    {
        $date = $date ?? today()->toDateString();

        $registers = CashRegister::whereDate('opened_at', $date)
            ->orWhereDate('closed_at', $date)
            ->with(['user', 'location'])
            ->get();

        $summaries = [];
        $totals = [
            'total_sales' => 0,
            'total_refunds' => 0,
            'net_sales' => 0,
            'cash_collected' => 0,
        ];

        foreach ($registers as $register) {
            $summary = $this->getRegisterSummary($register);
            $summaries[] = $summary;

            $totals['total_sales'] += $summary['total_sales'];
            $totals['total_refunds'] += $summary['total_refunds'];
            $totals['net_sales'] += $summary['net_sales'];

            if ($register->isClosed()) {
                $totals['cash_collected'] += $register->closing_cash ?? 0;
            }
        }

        return [
            'date' => $date,
            'registers' => $summaries,
            'totals' => $totals,
            'registers_count' => $registers->count(),
            'open_registers' => $registers->where('status', CashRegister::STATUS_OPEN)->count(),
        ];
    }

    /**
     * Get denomination options for counting cash
     */
    public function getDenominations(?string $currency = null): array
    {
        // Can be configured per currency/store
        return [
            ['value' => 100, 'label' => '$100'],
            ['value' => 50, 'label' => '$50'],
            ['value' => 20, 'label' => '$20'],
            ['value' => 10, 'label' => '$10'],
            ['value' => 5, 'label' => '$5'],
            ['value' => 2, 'label' => '$2'],
            ['value' => 1, 'label' => '$1'],
            ['value' => 0.50, 'label' => '50¢'],
            ['value' => 0.25, 'label' => '25¢'],
            ['value' => 0.10, 'label' => '10¢'],
            ['value' => 0.05, 'label' => '5¢'],
            ['value' => 0.01, 'label' => '1¢'],
        ];
    }

    /**
     * Calculate total from denominations
     */
    public function calculateFromDenominations(array $denominations): float
    {
        $total = 0;

        foreach ($denominations as $value => $count) {
            $total += (float) $value * (int) $count;
        }

        return round($total, 2);
    }

    /**
     * Validate register can be closed
     */
    public function canCloseRegister(CashRegister $register): array
    {
        $errors = [];

        if (!$register->isOpen()) {
            $errors[] = 'Register is not open';
        }

        if ($register->user_id !== auth()->id()) {
            $errors[] = 'You can only close your own register';
        }

        return $errors;
    }

    /**
     * Force close all open registers for a user
     */
    public function forceCloseUserRegisters(int $userId): int
    {
        $registers = CashRegister::open()->forUser($userId)->get();
        $count = 0;

        foreach ($registers as $register) {
            $register->close(
                $register->calculateExpectedCash(),
                null,
                'Force closed by system'
            );
            $count++;
        }

        return $count;
    }
}
