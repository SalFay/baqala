<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyTier;
use App\Models\CustomerLoyalty;
use App\Models\LoyaltyTransaction;
use App\Models\Customer;
use App\Services\Loyalty\LoyaltyService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoyaltyController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected LoyaltyService $loyaltyService
    ) {}

    /**
     * Get loyalty program overview
     */
    public function overview(): JsonResponse
    {
        $stats = [
            'total_members' => CustomerLoyalty::where('is_active', true)->count(),
            'total_points_issued' => CustomerLoyalty::sum('points_earned_total'),
            'total_points_redeemed' => CustomerLoyalty::sum('points_redeemed_total'),
            'total_points_balance' => CustomerLoyalty::sum('points_balance'),
            'tiers' => LoyaltyTier::withCount(['customerLoyalties' => fn($q) => $q->where('is_active', true)])
                ->orderBy('min_points')
                ->get(),
        ];

        return $this->success($stats);
    }

    /**
     * List loyalty tiers
     */
    public function tiers(): JsonResponse
    {
        $tiers = LoyaltyTier::withCount('customerLoyalties')
            ->orderBy('min_points')
            ->get();

        return $this->success($tiers);
    }

    /**
     * Create loyalty tier
     */
    public function storeTier(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'min_points' => 'required|integer|min:0',
            'points_multiplier' => 'nullable|numeric|min:1|max:10',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'benefits' => 'nullable|array',
            'badge_color' => 'nullable|string|max:20',
            'badge_icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['points_multiplier'] = $validated['points_multiplier'] ?? 1;

        $tier = LoyaltyTier::create($validated);

        return $this->created($tier, 'Loyalty tier created successfully');
    }

    /**
     * Update loyalty tier
     */
    public function updateTier(Request $request, LoyaltyTier $tier): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'min_points' => 'sometimes|required|integer|min:0',
            'points_multiplier' => 'nullable|numeric|min:1|max:10',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'benefits' => 'nullable|array',
            'badge_color' => 'nullable|string|max:20',
            'badge_icon' => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $tier->update($validated);

        return $this->success($tier, 'Loyalty tier updated successfully');
    }

    /**
     * Delete loyalty tier
     */
    public function destroyTier(LoyaltyTier $tier): JsonResponse
    {
        // Check if tier has members
        if ($tier->customerLoyalties()->where('is_active', true)->exists()) {
            return $this->error('Cannot delete tier with active members', 422);
        }

        $tier->delete();

        return $this->success(null, 'Loyalty tier deleted successfully');
    }

    /**
     * Get customer's loyalty info
     */
    public function customerLoyalty(Customer $customer): JsonResponse
    {
        $loyalty = $customer->loyalty()->with('tier')->first();

        if (!$loyalty) {
            return $this->success([
                'enrolled' => false,
                'message' => 'Customer not enrolled in loyalty program',
            ]);
        }

        $transactions = $loyalty->transactions()
            ->latest()
            ->limit(10)
            ->get();

        return $this->success([
            'enrolled' => true,
            'loyalty' => $loyalty,
            'recent_transactions' => $transactions,
        ]);
    }

    /**
     * Enroll customer in loyalty program
     */
    public function enroll(Request $request, Customer $customer): JsonResponse
    {
        // Check if already enrolled
        if ($customer->loyalty()->exists()) {
            return $this->error('Customer already enrolled in loyalty program', 422);
        }

        $validated = $request->validate([
            'card_number' => 'nullable|string|max:50|unique:customer_loyalties,card_number',
        ]);

        $loyalty = $this->loyaltyService->enrollCustomer(
            $customer,
            $validated['card_number'] ?? null
        );

        return $this->created($loyalty->load('tier'), 'Customer enrolled in loyalty program');
    }

    /**
     * Award points to customer
     */
    public function awardPoints(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $loyalty = $customer->loyalty;

        if (!$loyalty) {
            return $this->error('Customer not enrolled in loyalty program', 422);
        }

        $transaction = $this->loyaltyService->awardPoints(
            $loyalty,
            $validated['points'],
            $validated['description'] ?? 'Manual points award'
        );

        return $this->success([
            'transaction' => $transaction,
            'new_balance' => $loyalty->fresh()->points_balance,
        ], 'Points awarded successfully');
    }

    /**
     * Redeem points
     */
    public function redeemPoints(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'nullable|string|max:500',
        ]);

        $loyalty = $customer->loyalty;

        if (!$loyalty) {
            return $this->error('Customer not enrolled in loyalty program', 422);
        }

        if ($loyalty->points_balance < $validated['points']) {
            return $this->error('Insufficient points balance', 422);
        }

        $transaction = $this->loyaltyService->redeemPoints(
            $loyalty,
            $validated['points'],
            $validated['description'] ?? 'Manual points redemption'
        );

        return $this->success([
            'transaction' => $transaction,
            'new_balance' => $loyalty->fresh()->points_balance,
        ], 'Points redeemed successfully');
    }

    /**
     * Calculate points for amount
     */
    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $multiplier = 1;

        if (!empty($validated['customer_id'])) {
            $customer = Customer::find($validated['customer_id']);
            $loyalty = $customer?->loyalty()->with('tier')->first();
            $multiplier = $loyalty?->tier?->points_multiplier ?? 1;
        }

        $points = $this->loyaltyService->calculatePoints(
            $validated['amount'],
            $multiplier
        );

        return $this->success([
            'amount' => $validated['amount'],
            'points' => $points,
            'multiplier' => $multiplier,
        ]);
    }

    /**
     * Get loyalty transactions
     */
    public function transactions(Request $request): JsonResponse
    {
        $query = LoyaltyTransaction::with(['customerLoyalty.customer', 'createdBy']);

        if ($customerId = $request->input('customer_id')) {
            $query->whereHas('customerLoyalty', fn($q) =>
                $q->where('customer_id', $customerId)
            );
        }

        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        if ($fromDate = $request->input('from_date')) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate = $request->input('to_date')) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $transactions = $query->latest()->paginate($request->input('per_page', 50));

        return $this->paginated($transactions);
    }

    /**
     * Get points value in currency
     */
    public function pointsValue(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'points' => 'required|integer|min:0',
        ]);

        $value = $this->loyaltyService->calculatePointsValue($validated['points']);

        return $this->success([
            'points' => $validated['points'],
            'value' => $value,
            'currency' => 'SAR',
        ]);
    }
}
