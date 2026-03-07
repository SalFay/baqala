<?php

namespace App\Http\Controllers;

use App\Models\TaxGroup;
use App\Models\TaxRate;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaxRateController extends Controller
{
    public function __construct(
        protected TaxService $taxService
    ) {}

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/TaxRates/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = TaxRate::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('tax_number', 'like', "%{$request->search}%");
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
            $query->orderBy('is_default', 'desc')->orderBy('name');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $taxRates = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($rate) => [
                'id' => $rate->id,
                'name' => $rate->name,
                'name_ar' => $rate->name_ar,
                'rate' => $rate->rate,
                'is_default' => $rate->is_default,
                'is_compound' => $rate->is_compound,
                'is_recoverable' => $rate->is_recoverable,
                'tax_number' => $rate->tax_number,
                'is_active' => $rate->is_active,
                'description' => $rate->description,
                'created_at' => $rate->created_at,
            ]);

        return response()->json([
            'data' => $taxRates,
            'total' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_compound' => 'boolean',
            'is_recoverable' => 'boolean',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // If this is set as default, unset others
        if ($validated['is_default'] ?? false) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        $validated['store_id'] = auth()->user()->store_id ?? 1;

        $taxRate = TaxRate::create($validated);

        return response()->json([
            'data' => $taxRate,
            'notifications' => [['type' => 'success', 'message' => 'Tax rate created successfully']],
        ], 201);
    }

    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'rate' => 'sometimes|numeric|min:0|max:100',
            'is_default' => 'boolean',
            'is_compound' => 'boolean',
            'is_recoverable' => 'boolean',
            'tax_number' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ]);

        // If this is set as default, unset others
        if (($validated['is_default'] ?? false) && !$taxRate->is_default) {
            TaxRate::where('is_default', true)->where('id', '!=', $taxRate->id)->update(['is_default' => false]);
        }

        $taxRate->update($validated);

        return response()->json([
            'data' => $taxRate->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Tax rate updated successfully']],
        ]);
    }

    public function destroy(TaxRate $taxRate): JsonResponse
    {
        if ($taxRate->is_default) {
            return response()->json([
                'notifications' => [['type' => 'error', 'message' => 'Cannot delete the default tax rate']],
            ], 422);
        }

        $taxRate->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Tax rate deleted successfully']],
        ]);
    }

    /**
     * Get all active tax rates for dropdown
     */
    public function all(): JsonResponse
    {
        $taxRates = $this->taxService->getActiveTaxRates();

        return response()->json([
            'data' => $taxRates->map(fn($rate) => [
                'id' => $rate->id,
                'name' => $rate->name,
                'rate' => $rate->rate,
                'is_default' => $rate->is_default,
            ]),
        ]);
    }

    // =====================
    // Tax Groups
    // =====================

    public function groupsIndex(Request $request): JsonResponse
    {
        $query = TaxGroup::query()->with('taxRates');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        $query->orderBy('name');

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $groups = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'name_ar' => $group->name_ar,
                'description' => $group->description,
                'is_active' => $group->is_active,
                'total_rate' => $group->total_rate,
                'tax_rates' => $group->taxRates->map(fn($rate) => [
                    'id' => $rate->id,
                    'name' => $rate->name,
                    'rate' => $rate->rate,
                ]),
                'created_at' => $group->created_at,
            ]);

        return response()->json([
            'data' => $groups,
            'total' => $total,
        ]);
    }

    public function groupStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'tax_rate_ids' => 'required|array|min:1',
            'tax_rate_ids.*' => 'exists:tax_rates,id',
        ]);

        $validated['store_id'] = auth()->user()->store_id ?? 1;

        $group = TaxGroup::create([
            'store_id' => $validated['store_id'],
            'name' => $validated['name'],
            'name_ar' => $validated['name_ar'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Attach tax rates with sort order
        $syncData = [];
        foreach ($validated['tax_rate_ids'] as $index => $taxRateId) {
            $syncData[$taxRateId] = ['sort_order' => $index];
        }
        $group->taxRates()->sync($syncData);

        return response()->json([
            'data' => $group->load('taxRates'),
            'notifications' => [['type' => 'success', 'message' => 'Tax group created successfully']],
        ], 201);
    }

    public function groupUpdate(Request $request, TaxGroup $taxGroup): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'tax_rate_ids' => 'sometimes|array|min:1',
            'tax_rate_ids.*' => 'exists:tax_rates,id',
        ]);

        $taxGroup->update([
            'name' => $validated['name'] ?? $taxGroup->name,
            'name_ar' => $validated['name_ar'] ?? $taxGroup->name_ar,
            'description' => $validated['description'] ?? $taxGroup->description,
            'is_active' => $validated['is_active'] ?? $taxGroup->is_active,
        ]);

        if (isset($validated['tax_rate_ids'])) {
            $syncData = [];
            foreach ($validated['tax_rate_ids'] as $index => $taxRateId) {
                $syncData[$taxRateId] = ['sort_order' => $index];
            }
            $taxGroup->taxRates()->sync($syncData);
        }

        return response()->json([
            'data' => $taxGroup->fresh()->load('taxRates'),
            'notifications' => [['type' => 'success', 'message' => 'Tax group updated successfully']],
        ]);
    }

    public function groupDestroy(TaxGroup $taxGroup): JsonResponse
    {
        $taxGroup->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Tax group deleted successfully']],
        ]);
    }

    /**
     * Get all active tax groups for dropdown
     */
    public function groupsAll(): JsonResponse
    {
        $groups = $this->taxService->getActiveTaxGroups();

        return response()->json([
            'data' => $groups->map(fn($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'total_rate' => $group->total_rate,
                'tax_rates' => $group->taxRates->pluck('name'),
            ]),
        ]);
    }
}
