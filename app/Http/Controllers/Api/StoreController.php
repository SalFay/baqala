<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    use ApiResponse;

    /**
     * List all stores
     */
    public function index(Request $request): JsonResponse
    {
        $query = Store::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_warehouse')) {
            $query->where('is_warehouse', filter_var($request->input('is_warehouse'), FILTER_VALIDATE_BOOLEAN));
        }

        $stores = $query->withCount(['users', 'orders', 'products'])
            ->orderBy('name')
            ->get();

        return $this->success($stores);
    }

    /**
     * Create a new store
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:stores,code',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'currency_code' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
            'is_warehouse' => 'nullable|boolean',
            'operating_hours' => 'nullable|array',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_warehouse'] = $validated['is_warehouse'] ?? false;
        $validated['currency_code'] = $validated['currency_code'] ?? 'SAR';
        $validated['timezone'] = $validated['timezone'] ?? 'Asia/Riyadh';

        $store = Store::create($validated);

        return $this->created($store, 'Store created successfully');
    }

    /**
     * Show store details
     */
    public function show(Store $store): JsonResponse
    {
        $store->load('users');
        $store->loadCount(['orders', 'products', 'inventories']);

        return $this->success($store);
    }

    /**
     * Update store
     */
    public function update(Request $request, Store $store): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'sometimes|required|string|max:20|unique:stores,code,' . $store->id,
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'manager_name' => 'nullable|string|max:255',
            'timezone' => 'nullable|string|max:50',
            'currency_code' => 'nullable|string|max:3',
            'is_active' => 'nullable|boolean',
            'is_warehouse' => 'nullable|boolean',
            'operating_hours' => 'nullable|array',
        ]);

        $store->update($validated);

        return $this->success($store, 'Store updated successfully');
    }

    /**
     * Delete store
     */
    public function destroy(Store $store): JsonResponse
    {
        // Check for related records
        if ($store->orders()->exists()) {
            return $this->error('Cannot delete store with orders', 422);
        }

        if ($store->inventories()->exists()) {
            return $this->error('Cannot delete store with inventory. Transfer inventory first.', 422);
        }

        // Detach users
        $store->users()->detach();
        $store->delete();

        return $this->success(null, 'Store deleted successfully');
    }

    /**
     * Get store inventory summary
     */
    public function inventory(Store $store, Request $request): JsonResponse
    {
        $query = $store->inventories()
            ->with(['product', 'productVariant']);

        if ($search = $request->input('search')) {
            $query->whereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->has('low_stock')) {
            $query->whereColumn('quantity', '<=', 'low_stock_threshold');
        }

        $inventory = $query->paginate($request->input('per_page', 50));

        return $this->paginated($inventory);
    }

    /**
     * Assign users to store
     */
    public function assignUsers(Request $request, Store $store): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        // Sync users, keeping existing primary flags where set
        $currentUsers = $store->users()->pluck('users.id')->toArray();
        $newUsers = array_diff($validated['user_ids'], $currentUsers);

        foreach ($newUsers as $userId) {
            $store->users()->attach($userId, ['is_primary' => false]);
        }

        // Remove users not in the new list
        $removeUsers = array_diff($currentUsers, $validated['user_ids']);
        $store->users()->detach($removeUsers);

        return $this->success($store->load('users'), 'Users assigned successfully');
    }

    /**
     * Get store statistics
     */
    public function stats(Store $store): JsonResponse
    {
        $stats = [
            'total_products' => $store->products()->count(),
            'total_orders' => $store->orders()->count(),
            'today_orders' => $store->orders()->whereDate('created_at', today())->count(),
            'today_sales' => $store->orders()
                ->whereDate('created_at', today())
                ->sum('total'),
            'low_stock_items' => $store->inventories()
                ->whereColumn('quantity', '<=', 'low_stock_threshold')
                ->count(),
            'active_users' => $store->users()->where('status', 'active')->count(),
        ];

        return $this->success($stats);
    }

    /**
     * Toggle store active status
     */
    public function toggleStatus(Store $store): JsonResponse
    {
        $store->is_active = !$store->is_active;
        $store->save();

        return $this->success($store, 'Store status updated');
    }
}
