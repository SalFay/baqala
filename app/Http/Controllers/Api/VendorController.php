<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Vendor\StoreVendorRequest;
use App\Http\Requests\Api\Vendor\UpdateVendorRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $query = Vendor::query()
            ->withCount(['products', 'purchaseOrders']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $vendors = $query->paginate($request->input('per_page', 20));

        return VendorResource::collection($vendors)->response();
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = Vendor::create($request->validated());

        return VendorResource::make($vendor)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        return VendorResource::make($vendor)->response();
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $vendor->update($request->validated());

        return VendorResource::make($vendor->fresh())->response();
    }

    public function destroy(Vendor $vendor): JsonResponse
    {
        // Check if vendor has purchase orders
        if ($vendor->purchaseOrders()->exists()) {
            return response()->json([
                'message' => 'Cannot delete vendor with purchase orders'
            ], 422);
        }

        $vendor->delete();

        return response()->json(['message' => 'Vendor deleted successfully']);
    }

    public function search(Request $request): JsonResponse
    {
        $search = $request->input('q', '');

        $vendors = Vendor::where('is_active', true)
            ->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->limit(20)
            ->get();

        return VendorResource::collection($vendors)->response();
    }

    public function purchaseOrders(Vendor $vendor, Request $request): JsonResponse
    {
        $orders = $vendor->purchaseOrders()
            ->with(['store', 'createdBy', 'currentStatus'])
            ->latest()
            ->paginate($request->input('per_page', 20));

        return PurchaseOrderResource::collection($orders)->response();
    }

    public function balance(Vendor $vendor): JsonResponse
    {
        return response()->json([
            'vendor_id' => $vendor->id,
            'vendor_name' => $vendor->name,
            'balance' => $vendor->balance,
        ]);
    }
}
