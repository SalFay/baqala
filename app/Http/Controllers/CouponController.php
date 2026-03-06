<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Customer;
use App\Services\DiscountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    public function __construct(
        protected DiscountService $discountService
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Marketing/Coupons/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('code', 'like', "%{$request->search}%")
                    ->orWhere('name', 'like', "%{$request->search}%");
            });
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
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

        $coupons = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($coupon) => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'name' => $coupon->name,
                'description' => $coupon->description,
                'discount_type' => $coupon->discount_type,
                'discount_amount' => $coupon->discount_amount,
                'discount_display' => $coupon->discount_display,
                'applies_to' => $coupon->applies_to,
                'min_order_amount' => $coupon->min_order_amount,
                'max_discount_amount' => $coupon->max_discount_amount,
                'first_order_only' => $coupon->first_order_only,
                'max_uses' => $coupon->max_uses,
                'max_uses_per_customer' => $coupon->max_uses_per_customer,
                'current_uses' => $coupon->current_uses,
                'starts_at' => $coupon->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $coupon->ends_at?->format('Y-m-d H:i'),
                'is_valid' => $coupon->isValid(),
                'is_active' => $coupon->is_active,
                'created_at' => $coupon->created_at,
            ]);

        return response()->json([
            'data' => $coupons,
            'total' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'nullable|string|max:50|unique:coupons,code',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:fixed,percentage,free_shipping',
            'discount_amount' => 'required_unless:discount_type,free_shipping|nullable|numeric|min:0.01',
            'applies_to' => 'required|in:all,category,brand,product',
            'applies_to_ids' => 'nullable|array',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'customer_ids' => 'nullable|array',
            'customer_group_ids' => 'nullable|array',
            'first_order_only' => 'boolean',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $coupon = Coupon::create($validated);

        return response()->json([
            'data' => $coupon,
            'notifications' => [['type' => 'success', 'message' => 'Coupon created successfully']],
        ], 201);
    }

    public function update(Request $request, Coupon $coupon): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|max:50|unique:coupons,code,' . $coupon->id,
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'sometimes|in:fixed,percentage,free_shipping',
            'discount_amount' => 'nullable|numeric|min:0.01',
            'applies_to' => 'sometimes|in:all,category,brand,product',
            'applies_to_ids' => 'nullable|array',
            'min_order_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'customer_ids' => 'nullable|array',
            'customer_group_ids' => 'nullable|array',
            'first_order_only' => 'boolean',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $coupon->update($validated);

        return response()->json([
            'data' => $coupon->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Coupon updated successfully']],
        ]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        if ($coupon->current_uses > 0) {
            // Soft delete if already used
            $coupon->delete();
        } else {
            // Force delete if never used
            $coupon->forceDelete();
        }

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Coupon deleted successfully']],
        ]);
    }

    /**
     * Validate a coupon code (called from POS)
     */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'items' => 'required|array',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        $customer = $request->customer_id
            ? Customer::find($request->customer_id)
            : null;

        $result = $this->discountService->applyCoupon(
            $request->code,
            $request->items,
            $customer
        );

        if (!$result['success']) {
            return response()->json([
                'valid' => false,
                'message' => $result['error'],
            ]);
        }

        return response()->json([
            'valid' => true,
            'coupon' => $result['coupon'],
            'discount' => $result['discount'],
            'free_shipping' => $result['free_shipping'],
        ]);
    }

    /**
     * Generate a unique coupon code
     */
    public function generateCode(): JsonResponse
    {
        return response()->json([
            'code' => Coupon::generateCode(),
        ]);
    }

    /**
     * Get coupon usage statistics
     */
    public function statistics(Coupon $coupon): JsonResponse
    {
        $usages = $coupon->usages()
            ->with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        $totalDiscountGiven = $coupon->usages()->sum('discount_applied');
        $uniqueCustomers = $coupon->usages()->distinct('customer_id')->count('customer_id');

        return response()->json([
            'total_uses' => $coupon->current_uses,
            'total_discount_given' => $totalDiscountGiven,
            'unique_customers' => $uniqueCustomers,
            'recent_usages' => $usages->map(fn($usage) => [
                'id' => $usage->id,
                'customer' => $usage->customer ? [
                    'id' => $usage->customer->id,
                    'name' => $usage->customer->name,
                ] : null,
                'order_id' => $usage->order_id,
                'discount_applied' => $usage->discount_applied,
                'used_at' => $usage->created_at->format('Y-m-d H:i'),
            ]),
        ]);
    }
}
