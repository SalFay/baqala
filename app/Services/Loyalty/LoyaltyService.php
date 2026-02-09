<?php

namespace App\Services\Loyalty;

use App\Models\Customer;
use App\Models\CustomerLoyalty;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;

class LoyaltyService
{
    public function calculatePointsForPurchase(float $amount): int
    {
        $pointsPerUnit = (float) Setting::get('loyalty_points_per_sar', 1);
        return (int) floor($amount * $pointsPerUnit);
    }

    public function getPointValue(): float
    {
        return (float) Setting::get('loyalty_point_value', 0.01);
    }

    public function awardPoints(
        Customer $customer,
        int $points,
        $reference = null,
        ?string $description = null
    ): LoyaltyTransaction {
        $loyalty = $customer->getOrCreateLoyalty();

        return $loyalty->addPoints(
            $points,
            $reference ? get_class($reference) : null,
            $reference?->id,
            $description ?? 'Points earned from purchase'
        );
    }

    public function redeemPoints(
        Customer $customer,
        int $points,
        $reference = null,
        ?string $description = null
    ): LoyaltyTransaction {
        $loyalty = $customer->getOrCreateLoyalty();

        return $loyalty->redeemPoints(
            $points,
            $reference ? get_class($reference) : null,
            $reference?->id,
            $description ?? 'Points redeemed'
        );
    }

    public function deductPoints(
        Customer $customer,
        int $points,
        $reference = null,
        ?string $reason = null
    ): LoyaltyTransaction {
        $loyalty = $customer->getOrCreateLoyalty();

        $loyalty->points_balance = max(0, $loyalty->points_balance - $points);
        $loyalty->save();

        return LoyaltyTransaction::create([
            'customer_loyalty_id' => $loyalty->id,
            'type' => 'adjust',
            'points' => -$points,
            'points_balance_after' => $loyalty->points_balance,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'description' => $reason ?? 'Points adjustment',
            'created_by' => Auth::id(),
        ]);
    }

    public function refundPoints(
        Customer $customer,
        int $points,
        $reference = null
    ): LoyaltyTransaction {
        $loyalty = $customer->getOrCreateLoyalty();

        $loyalty->points_balance += $points;
        $loyalty->save();

        return LoyaltyTransaction::create([
            'customer_loyalty_id' => $loyalty->id,
            'type' => 'refund',
            'points' => $points,
            'points_balance_after' => $loyalty->points_balance,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'description' => 'Points refunded',
            'created_by' => Auth::id(),
        ]);
    }

    public function getCustomerLoyaltyInfo(Customer $customer): array
    {
        $loyalty = $customer->loyalty;

        if (!$loyalty) {
            return [
                'has_loyalty' => false,
                'points_balance' => 0,
                'tier' => null,
            ];
        }

        return [
            'has_loyalty' => true,
            'card_number' => $loyalty->card_number,
            'points_balance' => $loyalty->points_balance,
            'points_value' => $loyalty->points_balance * $this->getPointValue(),
            'points_earned_total' => $loyalty->points_earned_total,
            'points_redeemed_total' => $loyalty->points_redeemed_total,
            'lifetime_spend' => $loyalty->lifetime_spend,
            'tier' => $loyalty->tier ? [
                'name' => $loyalty->tier->name,
                'discount_percentage' => $loyalty->tier->discount_percentage,
                'points_multiplier' => $loyalty->tier->points_multiplier,
                'badge_color' => $loyalty->tier->badge_color,
            ] : null,
        ];
    }

    public function calculateRedemptionValue(int $points): float
    {
        return $points * $this->getPointValue();
    }

    public function getMaxRedeemablePoints(Customer $customer, float $orderTotal): int
    {
        $loyalty = $customer->loyalty;

        if (!$loyalty) {
            return 0;
        }

        $maxRedeemablePercent = (float) Setting::get('loyalty_max_redeem_percent', 50);
        $maxValueToRedeem = ($orderTotal * $maxRedeemablePercent) / 100;
        $maxPointsForValue = (int) floor($maxValueToRedeem / $this->getPointValue());

        return min($loyalty->points_balance, $maxPointsForValue);
    }
}
