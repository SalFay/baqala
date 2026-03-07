<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Coupon\StoreCouponRequest;
use App\Http\Requests\Api\Coupon\UpdateCouponRequest;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Customer;
use App\Services\DiscountService;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CouponController extends Controller
{
    use HasListing;

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
        return $this->getListing(
            $request,
            Coupon::class,
            resource: CouponResource::class,
            options: [
                'searchColumns' => ['code', 'name'],
                'filterColumns' => [
                    'discount_type' => 'exact',
                    'applies_to' => 'exact',
                    'is_active' => 'exact',
                ],
                'defaultSort' => 'created_at',
                'defaultSortDir' => 'desc',
            ]
        );
    }

    public function store(StoreCouponRequest $request): JsonResponse
    {
        $coupon = Coupon::create($request->validated());

        return response()->json([
            'data' => new CouponResource($coupon),
            'notifications' => [['type' => 'success', 'message' => 'Coupon created successfully']],
        ], 201);
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): JsonResponse
    {
        $coupon->update($request->validated());

        return response()->json([
            'data' => new CouponResource($coupon->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Coupon updated successfully']],
        ]);
    }

    public function destroy(Coupon $coupon): JsonResponse
    {
        if ($coupon->current_uses > 0) {
            $coupon->delete();
        } else {
            $coupon->forceDelete();
        }

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Coupon deleted successfully']],
        ]);
    }

    public function validateCoupon(Request $request): JsonResponse
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

    public function generateCode(): JsonResponse
    {
        return response()->json([
            'code' => Coupon::generateCode(),
        ]);
    }

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
