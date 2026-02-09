<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VendorController extends Controller
{
    use ApiResponse;

    /**
     * List all vendors with optional filters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query();

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $vendors = $query->paginate($request->input('per_page', 20));

        return $this->paginated($vendors);
    }

    /**
     * Create a new vendor
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $vendor = Vendor::create($validated);

        return $this->created($vendor, 'Vendor created successfully');
    }

    /**
     * Show vendor details
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['paymentMethods', 'account']);

        return $this->success($vendor);
    }

    /**
     * Update vendor
     */
    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'status' => 'nullable|in:active,inactive',
        ]);

        $vendor->update($validated);

        return $this->success($vendor, 'Vendor updated successfully');
    }

    /**
     * Delete vendor
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        // Check if vendor has purchase orders
        if ($vendor->purchaseOrders()->exists()) {
            return $this->error('Cannot delete vendor with purchase orders', 422);
        }

        $vendor->delete();

        return $this->success(null, 'Vendor deleted successfully');
    }

    /**
     * Search vendors (for dropdowns)
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->input('q', '');

        $vendors = Vendor::where('status', 'active')
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get(['id', 'name', 'mobile']);

        return $this->success($vendors);
    }

    /**
     * Get vendor's purchase orders
     */
    public function purchaseOrders(Vendor $vendor, Request $request): JsonResponse
    {
        $orders = $vendor->purchaseOrders()
            ->with(['store', 'createdBy'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return $this->paginated($orders);
    }

    /**
     * Get vendor balance/account info
     */
    public function balance(Vendor $vendor): JsonResponse
    {
        $account = $vendor->account()->first();

        return $this->success([
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'balance' => $account->balance ?? 0,
            'account' => $account,
        ]);
    }
}
