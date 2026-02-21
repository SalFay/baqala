<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Vendor\StoreVendorRequest;
use App\Http\Requests\Api\Vendor\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    /**
     * Server-side listing for DataGridTable
     */
    public function listing(Request $request): JsonResponse
    {
        $query = Vendor::query();

        // Search
        if ($request->search) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            });
        }

        // Soft deleted filter
        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        // Sorting
        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('name', 'asc');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $vendors = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email,
                'phone' => $vendor->phone,
                'address' => $vendor->address,
                'status' => $vendor->status,
                'created_at' => $vendor->created_at,
            ]);

        return response()->json([
            'data' => $vendors,
            'total' => $total,
        ]);
    }

    public function index(Request $request): Response|JsonResponse
    {
        $vendors = Vendor::query()
            ->when($request->search, function ($q, $term) {
                $q->where(function ($q) use ($term) {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%");
                });
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->orderBy($request->sort_by ?? 'name', $request->sort_dir ?? 'asc')
            ->paginate($request->per_page ?? 20);

        $vendorsData = $vendors->map(fn($vendor) => [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'email' => $vendor->email,
            'phone' => $vendor->phone,
            'address' => $vendor->address,
            'status' => $vendor->status,
            'created_at' => $vendor->created_at,
        ]);

        // Return JSON for API requests (POS app)
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $vendorsData,
                'current_page' => $vendors->currentPage(),
                'per_page' => $vendors->perPage(),
                'total' => $vendors->total(),
                'last_page' => $vendors->lastPage(),
            ]);
        }

        return Inertia::render('Vendors/Index', [
            'vendors' => [
                'data' => $vendorsData,
                'meta' => [
                    'total' => $vendors->total(),
                    'per_page' => $vendors->perPage(),
                    'current_page' => $vendors->currentPage(),
                ],
            ],
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vendors/Create');
    }

    public function store(StoreVendorRequest $request): RedirectResponse|JsonResponse
    {
        $vendor = Vendor::create($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $vendor,
                'message' => 'Vendor created successfully.',
            ], 201);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor, Request $request): Response|JsonResponse
    {
        $vendor->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);

        $vendorData = [
            'id' => $vendor->id,
            'name' => $vendor->name,
            'email' => $vendor->email,
            'phone' => $vendor->phone,
            'address' => $vendor->address,
            'city' => $vendor->city,
            'country' => $vendor->country,
            'tax_number' => $vendor->tax_number,
            'status' => $vendor->status,
            'recent_orders' => $vendor->purchaseOrders->map(fn($po) => [
                'id' => $po->id,
                'po_number' => $po->po_number,
                'total' => $po->total,
                'status' => $po->status,
                'created_at' => $po->created_at,
            ]),
            'created_at' => $vendor->created_at,
        ];

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json(['data' => $vendorData]);
        }

        return Inertia::render('Vendors/Show', [
            'vendor' => $vendorData,
        ]);
    }

    public function edit(Vendor $vendor): Response
    {
        return Inertia::render('Vendors/Edit', [
            'vendor' => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'email' => $vendor->email,
                'phone' => $vendor->phone,
                'address' => $vendor->address,
                'city' => $vendor->city,
                'country' => $vendor->country,
                'tax_number' => $vendor->tax_number,
                'status' => $vendor->status,
            ],
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse|JsonResponse
    {
        $vendor->update($request->validated());

        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'data' => $vendor->fresh(),
                'message' => 'Vendor updated successfully.',
            ]);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor): RedirectResponse|JsonResponse
    {
        $vendor->delete();

        if (request()->wantsJson() || request()->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'message' => 'Vendor deleted successfully.',
            ]);
        }

        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }
}
