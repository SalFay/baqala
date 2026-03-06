<?php

namespace App\Http\Controllers;

use App\Models\SellingPriceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellingPriceGroupController extends Controller
{
    /**
     * Display listing page or return JSON for DataGridTable.
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->listing($request);
        }

        return Inertia::render('Settings/PriceGroups/Index');
    }

    /**
     * Server-side listing for DataGridTable.
     */
    public function listing(Request $request): JsonResponse
    {
        $query = SellingPriceGroup::query()->withCount('customerGroups');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Soft deleted filter
        if ($request->boolean('soft_deleted')) {
            $query->onlyTrashed();
        }

        // Sorting
        if ($request->filled('sort') && is_array($request->sort)) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->ordered();
        }

        // Pagination
        $total = $query->count();
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 20);

        $data = $query->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'price_calculation_type' => $group->price_calculation_type,
                'price_calculation_amount' => $group->price_calculation_amount,
                'is_default' => $group->is_default,
                'is_active' => $group->is_active,
                'sort_order' => $group->sort_order,
                'customer_groups_count' => $group->customer_groups_count,
                'created_at' => $group->created_at?->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * List all active price groups (for dropdowns).
     */
    public function all(): JsonResponse
    {
        $groups = SellingPriceGroup::active()
            ->ordered()
            ->get()
            ->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'description' => $group->description,
                'price_calculation_type' => $group->price_calculation_type,
                'price_calculation_amount' => $group->price_calculation_amount,
                'is_default' => $group->is_default,
            ]);

        return response()->json(['data' => $groups]);
    }

    /**
     * Store a new price group.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_calculation_type' => 'required|in:fixed,percentage',
            'price_calculation_amount' => 'required|numeric',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            SellingPriceGroup::where('is_default', true)->update(['is_default' => false]);
        }

        $group = SellingPriceGroup::create($validated);

        return response()->json([
            'data' => $group,
            'message' => 'Price group created successfully',
        ], 201);
    }

    /**
     * Update a price group.
     */
    public function update(Request $request, SellingPriceGroup $sellingPriceGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_calculation_type' => 'required|in:fixed,percentage',
            'price_calculation_amount' => 'required|numeric',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if (($validated['is_default'] ?? false) && !$sellingPriceGroup->is_default) {
            SellingPriceGroup::where('is_default', true)->update(['is_default' => false]);
        }

        $sellingPriceGroup->update($validated);

        return response()->json([
            'data' => $sellingPriceGroup,
            'message' => 'Price group updated successfully',
        ]);
    }

    /**
     * Delete a price group.
     */
    public function destroy(SellingPriceGroup $sellingPriceGroup): JsonResponse
    {
        // Check if any customer groups are using this price group
        if ($sellingPriceGroup->customerGroups()->exists()) {
            return response()->json([
                'message' => 'Cannot delete price group that is assigned to customer groups',
            ], 422);
        }

        $sellingPriceGroup->delete();

        return response()->json([
            'message' => 'Price group deleted successfully',
        ]);
    }
}
