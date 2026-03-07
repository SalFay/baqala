<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Location\StoreLocationRequest;
use App\Http\Requests\Api\Location\UpdateLocationRequest;
use App\Http\Resources\LocationResource;
use App\Models\Location;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Locations/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Location::class,
            with: ['sellingPriceGroup'],
            resource: LocationResource::class,
            options: [
                'searchColumns' => ['name', 'code', 'city'],
                'filterColumns' => [
                    'is_main' => 'exact',
                    'is_active' => 'exact',
                ],
                'defaultSort' => 'is_main',
                'defaultSortDir' => 'desc',
            ]
        );
    }

    public function store(StoreLocationRequest $request): JsonResponse
    {
        $data = $request->validated();

        // If this is set as main, unset others
        if ($data['is_main'] ?? false) {
            Location::where('is_main', true)->update(['is_main' => false]);
        }

        $data['store_id'] = auth()->user()->store_id ?? 1;

        $location = Location::create($data);

        return response()->json([
            'data' => new LocationResource($location),
            'notifications' => [['type' => 'success', 'message' => 'Location created successfully']],
        ], 201);
    }

    public function update(UpdateLocationRequest $request, Location $location): JsonResponse
    {
        $data = $request->validated();

        // If this is set as main, unset others
        if (($data['is_main'] ?? false) && !$location->is_main) {
            Location::where('is_main', true)->where('id', '!=', $location->id)->update(['is_main' => false]);
        }

        $location->update($data);

        return response()->json([
            'data' => new LocationResource($location->fresh()),
            'notifications' => [['type' => 'success', 'message' => 'Location updated successfully']],
        ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        $hasStock = $location->productStock()->where('quantity', '>', 0)->exists();
        $hasOrders = $location->orders()->exists();

        if ($hasStock || $hasOrders) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot delete location with existing stock or orders']],
            ], 422);
        }

        $location->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Location deleted successfully']],
        ]);
    }

    public function all(): JsonResponse
    {
        $locations = Location::active()
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => LocationResource::collection($locations),
        ]);
    }

    public function stockSummary(Location $location): JsonResponse
    {
        $totalProducts = $location->productStock()->distinct('product_id')->count('product_id');
        $totalQuantity = $location->productStock()->sum('quantity');
        $lowStock = $location->productStock()
            ->join('products', 'product_location_stock.product_id', '=', 'products.id')
            ->whereColumn('product_location_stock.quantity', '<=', 'products.reorder_level')
            ->count();
        $outOfStock = $location->productStock()->where('quantity', '<=', 0)->count();

        return response()->json([
            'total_products' => $totalProducts,
            'total_quantity' => $totalQuantity,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
        ]);
    }
}
