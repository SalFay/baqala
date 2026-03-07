<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\TimePricing\StoreTimePricingRequest;
use App\Http\Requests\Api\TimePricing\UpdateTimePricingRequest;
use App\Http\Resources\TimePricingResource;
use App\Models\TimePricing;
use App\Models\Store;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TimePricingController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/TimePricing/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $store = Store::first();

        return $this->getListing(
            $request,
            TimePricing::class,
            resource: TimePricingResource::class,
            options: [
                'searchColumns' => ['name', 'name_ar', 'description'],
                'filterColumns' => [
                    'is_active' => 'exact',
                    'discount_type' => 'exact',
                    'applies_to' => 'exact',
                ],
                'defaultSort' => 'priority',
                'defaultSortDir' => 'desc',
                'preFilter' => function ($query) use ($store) {
                    $query->forStore($store->id);
                },
            ]
        );
    }

    public function store(StoreTimePricingRequest $request): JsonResponse
    {
        $data = $request->validated();
        $store = Store::first();

        $timePricing = TimePricing::create([
            ...$data,
            'store_id' => $store->id,
        ]);

        return response()->json([
            'data' => new TimePricingResource($timePricing),
            'notifications' => [['type' => 'success', 'message' => 'Time-based pricing created successfully']],
        ], 201);
    }

    public function show(TimePricing $timePricing): JsonResponse
    {
        return response()->json([
            'data' => new TimePricingResource($timePricing),
        ]);
    }

    public function update(UpdateTimePricingRequest $request, TimePricing $timePricing): JsonResponse
    {
        $timePricing->update($request->validated());

        return response()->json([
            'data' => new TimePricingResource($timePricing),
            'notifications' => [['type' => 'success', 'message' => 'Time-based pricing updated successfully']],
        ]);
    }

    public function destroy(TimePricing $timePricing): JsonResponse
    {
        $timePricing->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Time-based pricing deleted successfully']],
        ]);
    }

    public function toggle(TimePricing $timePricing): JsonResponse
    {
        $timePricing->update(['is_active' => !$timePricing->is_active]);

        return response()->json([
            'data' => new TimePricingResource($timePricing),
            'notifications' => [[
                'type' => 'success',
                'message' => $timePricing->is_active ? 'Time pricing activated' : 'Time pricing deactivated'
            ]],
        ]);
    }

    /**
     * Get currently active time-based pricing rules
     */
    public function active(): JsonResponse
    {
        $store = Store::first();

        $activePricing = TimePricing::forStore($store->id)
            ->currentlyActive()
            ->orderByDesc('priority')
            ->get();

        return response()->json([
            'data' => TimePricingResource::collection($activePricing),
        ]);
    }

    /**
     * Preview what products would be affected by a time pricing rule
     */
    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'applies_to' => 'required|in:all,products,categories,brands',
            'product_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
            'brand_ids' => 'nullable|array',
            'discount_type' => 'required|in:percentage,fixed,special_price',
            'discount_value' => 'required|numeric|min:0',
        ]);

        $query = \App\Models\Product::query()
            ->with(['category', 'brand'])
            ->where('is_active', true);

        if ($validated['applies_to'] === 'products' && !empty($validated['product_ids'])) {
            $query->whereIn('id', $validated['product_ids']);
        } elseif ($validated['applies_to'] === 'categories' && !empty($validated['category_ids'])) {
            $query->whereIn('category_id', $validated['category_ids']);
        } elseif ($validated['applies_to'] === 'brands' && !empty($validated['brand_ids'])) {
            $query->whereIn('brand_id', $validated['brand_ids']);
        }

        $products = $query->limit(20)->get();

        $preview = $products->map(function ($product) use ($validated) {
            $originalPrice = $product->sale_price ?? $product->price;
            $discountedPrice = match ($validated['discount_type']) {
                'percentage' => $originalPrice * (1 - ($validated['discount_value'] / 100)),
                'fixed' => max(0, $originalPrice - $validated['discount_value']),
                'special_price' => $validated['discount_value'],
                default => $originalPrice,
            };

            return [
                'id' => $product->id,
                'name' => $product->name,
                'category' => $product->category?->name,
                'original_price' => $originalPrice,
                'discounted_price' => round($discountedPrice, 2),
                'savings' => round($originalPrice - $discountedPrice, 2),
            ];
        });

        return response()->json([
            'data' => [
                'products' => $preview,
                'total_affected' => $query->count(),
            ],
        ]);
    }
}
