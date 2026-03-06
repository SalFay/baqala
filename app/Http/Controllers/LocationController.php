<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Locations/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Location::query()->with('sellingPriceGroup');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('code', 'like', "%{$request->search}%")
                    ->orWhere('city', 'like', "%{$request->search}%");
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
            $query->orderBy('is_main', 'desc')->orderBy('name');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $locations = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($location) => [
                'id' => $location->id,
                'name' => $location->name,
                'code' => $location->code,
                'address' => $location->address,
                'city' => $location->city,
                'state' => $location->state,
                'country' => $location->country,
                'phone' => $location->phone,
                'email' => $location->email,
                'is_main' => $location->is_main,
                'is_active' => $location->is_active,
                'selling_price_group' => $location->sellingPriceGroup?->name,
                'invoice_prefix' => $location->invoice_prefix,
                'created_at' => $location->created_at,
            ]);

        return response()->json([
            'data' => $locations,
            'total' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:locations,code',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
            'selling_price_group_id' => 'nullable|exists:selling_price_groups,id',
            'invoice_prefix' => 'nullable|string|max:10',
            'settings' => 'nullable|array',
        ]);

        // If this is set as main, unset others
        if ($validated['is_main'] ?? false) {
            Location::where('is_main', true)->update(['is_main' => false]);
        }

        $validated['store_id'] = auth()->user()->store_id ?? 1;

        $location = Location::create($validated);

        return response()->json([
            'data' => $location,
            'notifications' => [['type' => 'success', 'message' => 'Location created successfully']],
        ], 201);
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'code' => 'sometimes|string|max:50|unique:locations,code,' . $location->id,
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'is_main' => 'boolean',
            'is_active' => 'boolean',
            'selling_price_group_id' => 'nullable|exists:selling_price_groups,id',
            'invoice_prefix' => 'nullable|string|max:10',
            'settings' => 'nullable|array',
        ]);

        // If this is set as main, unset others
        if (($validated['is_main'] ?? false) && !$location->is_main) {
            Location::where('is_main', true)->where('id', '!=', $location->id)->update(['is_main' => false]);
        }

        $location->update($validated);

        return response()->json([
            'data' => $location->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Location updated successfully']],
        ]);
    }

    public function destroy(Location $location): JsonResponse
    {
        // Check if location has stock or orders
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

    /**
     * Get all active locations for dropdown
     */
    public function all(): JsonResponse
    {
        $locations = Location::active()
            ->orderBy('is_main', 'desc')
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'is_main']);

        return response()->json($locations);
    }

    /**
     * Get stock summary for a location
     */
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
