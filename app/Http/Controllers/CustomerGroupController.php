<?php

namespace App\Http\Controllers;

use App\Models\CustomerGroup;
use App\Models\SellingPriceGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerGroupController extends Controller
{
    /**
     * Display listing page or return JSON for DataGridTable.
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->listing($request);
        }

        $priceGroups = SellingPriceGroup::active()->ordered()->get(['id', 'name']);

        return Inertia::render('Customers/Groups/Index', [
            'priceGroups' => $priceGroups,
        ]);
    }

    /**
     * Server-side listing for DataGridTable.
     */
    public function listing(Request $request): JsonResponse
    {
        $query = CustomerGroup::query()
            ->with('sellingPriceGroup:id,name')
            ->withCount('customers');

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
                'selling_price_group_id' => $group->selling_price_group_id,
                'selling_price_group' => $group->sellingPriceGroup?->name,
                'discount_percent' => $group->discount_percent,
                'is_default' => $group->is_default,
                'is_active' => $group->is_active,
                'sort_order' => $group->sort_order,
                'customers_count' => $group->customers_count,
                'created_at' => $group->created_at?->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * List all active customer groups (for dropdowns).
     */
    public function all(): JsonResponse
    {
        $groups = CustomerGroup::active()
            ->ordered()
            ->get()
            ->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'discount_percent' => $group->discount_percent,
                'is_default' => $group->is_default,
            ]);

        return response()->json(['data' => $groups]);
    }

    /**
     * Store a new customer group.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selling_price_group_id' => 'nullable|exists:selling_price_groups,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if ($validated['is_default'] ?? false) {
            CustomerGroup::where('is_default', true)->update(['is_default' => false]);
        }

        $group = CustomerGroup::create($validated);

        return response()->json([
            'data' => $group,
            'message' => 'Customer group created successfully',
        ], 201);
    }

    /**
     * Update a customer group.
     */
    public function update(Request $request, CustomerGroup $customerGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'selling_price_group_id' => 'nullable|exists:selling_price_groups,id',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        // If setting as default, unset other defaults
        if (($validated['is_default'] ?? false) && !$customerGroup->is_default) {
            CustomerGroup::where('is_default', true)->update(['is_default' => false]);
        }

        $customerGroup->update($validated);

        return response()->json([
            'data' => $customerGroup,
            'message' => 'Customer group updated successfully',
        ]);
    }

    /**
     * Delete a customer group.
     */
    public function destroy(CustomerGroup $customerGroup): JsonResponse
    {
        // Check if any customers are using this group
        if ($customerGroup->customers()->exists()) {
            return response()->json([
                'message' => 'Cannot delete customer group that has customers assigned',
            ], 422);
        }

        $customerGroup->delete();

        return response()->json([
            'message' => 'Customer group deleted successfully',
        ]);
    }
}
