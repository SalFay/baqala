<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Vendor\StoreVendorRequest;
use App\Http\Requests\Api\Vendor\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Vendors/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Vendor::class,
            resource: VendorResource::class,
            options: [
                'searchColumns' => ['name', 'email', 'phone', 'contact_name'],
                'filterColumns' => [
                    'is_active' => 'exact',
                ],
                'defaultSort' => 'name',
                'defaultSortDir' => 'asc',
            ]
        );
    }

    public function show(Vendor $vendor, Request $request): Response|JsonResponse
    {
        $vendor->load(['purchaseOrders' => fn($q) => $q->latest()->limit(10)]);

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new VendorResource($vendor),
            ]);
        }

        return Inertia::render('Vendors/Show', [
            'vendor' => new VendorResource($vendor),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Vendors/Create');
    }

    public function edit(Vendor $vendor): Response
    {
        return Inertia::render('Vendors/Edit', [
            'vendor' => new VendorResource($vendor),
        ]);
    }

    public function store(StoreVendorRequest $request): RedirectResponse|JsonResponse
    {
        $vendor = Vendor::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new VendorResource($vendor),
                'notifications' => [['type' => 'success', 'message' => 'Vendor created successfully']],
            ], 201);
        }

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse|JsonResponse
    {
        $vendor->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new VendorResource($vendor->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Vendor updated successfully']],
            ]);
        }

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor): RedirectResponse|JsonResponse
    {
        $vendor->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'notifications' => [['type' => 'success', 'message' => 'Vendor deleted successfully']],
            ]);
        }

        return redirect()->route('vendors.index')
            ->with('success', 'Vendor deleted successfully.');
    }
}
