<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Stock;
use App\Models\Vendor;

class AccountingService
{
    /**
     * Record a customer sale transaction
     */
    public function recordSale(
        Customer $customer,
        Order $order,
        float $total,
        float $paid,
        int $bankId = 0
    ): void {
        // Debit entry: customer owes total amount
        Account::create([
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'debit' => $total,
            'bank_id' => 0,
            'comments' => 'Order No.' . $order->id,
        ]);

        // Credit entry: payment received (if any)
        if ($paid > 0) {
            Account::create([
                'party_type' => Customer::class,
                'party_id' => $customer->id,
                'credit' => $paid,
                'bank_id' => $bankId,
                'comments' => 'Order No.' . $order->id,
            ]);
        }
    }

    /**
     * Record order update transaction
     */
    public function recordOrderUpdate(
        Customer $customer,
        Order $order,
        float $total,
        float $paid,
        int $bankId = 0
    ): void {
        Account::create([
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'debit' => $total,
            'bank_id' => 0,
            'comments' => 'Updated Order No.' . $order->id,
        ]);

        if ($paid > 0) {
            Account::create([
                'party_type' => Customer::class,
                'party_id' => $customer->id,
                'credit' => $paid,
                'bank_id' => $bankId,
                'comments' => 'Updated Order No.' . $order->id,
            ]);
        }
    }

    /**
     * Record order cancellation/deletion
     */
    public function recordOrderCancellation(Customer $customer, Order $order): void
    {
        Account::create([
            'party_type' => Customer::class,
            'party_id' => $customer->id,
            'credit' => $order->total,
            'comments' => 'Order No.' . $order->id . ' Deleted',
        ]);
    }

    /**
     * Record a vendor purchase transaction
     */
    public function recordPurchase(
        Vendor $vendor,
        Stock $stock,
        float $total,
        float $paid,
        float $discount = 0,
        float $deliveryCharges = 0,
        int $bankId = 0
    ): void {
        // Credit entry: we owe vendor the total + discount + delivery
        Account::create([
            'party_type' => Vendor::class,
            'party_id' => $vendor->id,
            'credit' => $total + $discount + $deliveryCharges,
            'comments' => 'Stock No.' . $stock->id,
        ]);

        // Debit entry for discount and delivery charges
        if ($discount + $deliveryCharges > 0) {
            Account::create([
                'party_type' => Vendor::class,
                'party_id' => $vendor->id,
                'debit' => $discount + $deliveryCharges,
                'comments' => 'Stock No.' . $stock->id,
            ]);
        }

        // Payment made to vendor (if any)
        if ($paid > 0) {
            Account::create([
                'party_type' => Vendor::class,
                'party_id' => $vendor->id,
                'debit' => $paid,
                'bank_id' => $bankId,
                'comments' => 'Stock No.' . $stock->id,
            ]);
        }
    }

    /**
     * Get customer balance
     */
    public function getCustomerBalance(int $customerId): float
    {
        $statement = Account::where('party_type', Customer::class)
            ->where('party_id', $customerId)
            ->get();

        $total = 0;
        foreach ($statement as $entry) {
            $total += $entry->debit - $entry->credit;
        }

        return $total;
    }

    /**
     * Get vendor balance
     */
    public function getVendorBalance(int $vendorId): float
    {
        $statement = Account::where('party_type', Vendor::class)
            ->where('party_id', $vendorId)
            ->get();

        $total = 0;
        foreach ($statement as $entry) {
            $total += $entry->credit - $entry->debit;
        }

        return $total;
    }
}
