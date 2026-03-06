<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Units/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = Unit::with('baseUnit');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('short_name', 'like', "%{$request->search}%");
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
            $query->orderBy('sort_order')->orderBy('name');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $units = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
                'is_base_unit' => $unit->is_base_unit,
                'base_unit_id' => $unit->base_unit_id,
                'base_unit' => $unit->baseUnit ? [
                    'id' => $unit->baseUnit->id,
                    'name' => $unit->baseUnit->name,
                    'short_name' => $unit->baseUnit->short_name,
                ] : null,
                'conversion_rate' => (float) $unit->conversion_rate,
                'allow_decimal' => $unit->allow_decimal,
                'products_count' => $unit->products()->count(),
                'derived_units_count' => $unit->derivedUnits()->count(),
                'is_active' => $unit->is_active,
                'sort_order' => $unit->sort_order,
                'created_at' => $unit->created_at,
            ]);

        return response()->json([
            'data' => $units,
            'total' => $total,
        ]);
    }

    public function all(): JsonResponse
    {
        $units = Unit::active()
            ->ordered()
            ->get()
            ->map(fn($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
                'is_base_unit' => $unit->is_base_unit,
                'base_unit_id' => $unit->base_unit_id,
                'conversion_rate' => (float) $unit->conversion_rate,
                'allow_decimal' => $unit->allow_decimal,
            ]);

        return response()->json(['data' => $units]);
    }

    public function baseUnits(): JsonResponse
    {
        $units = Unit::active()
            ->baseUnits()
            ->ordered()
            ->get()
            ->map(fn($unit) => [
                'id' => $unit->id,
                'name' => $unit->name,
                'short_name' => $unit->short_name,
            ]);

        return response()->json(['data' => $units]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:20',
            'is_base_unit' => 'boolean',
            'base_unit_id' => 'nullable|required_if:is_base_unit,false|exists:units,id',
            'conversion_rate' => 'nullable|numeric|min:0.0001|max:9999999999',
            'allow_decimal' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Ensure conversion rate is 1 for base units
        if ($validated['is_base_unit'] ?? true) {
            $validated['conversion_rate'] = 1;
            $validated['base_unit_id'] = null;
        }

        $unit = Unit::create($validated);

        return response()->json([
            'data' => $unit,
            'notifications' => [['type' => 'success', 'message' => 'Unit created successfully']],
        ], 201);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'short_name' => 'sometimes|string|max:20',
            'is_base_unit' => 'boolean',
            'base_unit_id' => 'nullable|exists:units,id',
            'conversion_rate' => 'nullable|numeric|min:0.0001|max:9999999999',
            'allow_decimal' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Prevent circular reference
        if (isset($validated['base_unit_id']) && $validated['base_unit_id'] == $unit->id) {
            return response()->json([
                'message' => 'A unit cannot be its own base unit.',
            ], 422);
        }

        // Ensure conversion rate is 1 for base units
        if ($validated['is_base_unit'] ?? $unit->is_base_unit) {
            $validated['conversion_rate'] = 1;
            $validated['base_unit_id'] = null;
        }

        $unit->update($validated);

        return response()->json([
            'data' => $unit->fresh('baseUnit'),
            'notifications' => [['type' => 'success', 'message' => 'Unit updated successfully']],
        ]);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $productUsage = $unit->products()->count();
        $derivedCount = $unit->derivedUnits()->count();

        if ($productUsage > 0) {
            return response()->json([
                'message' => "Cannot delete unit. It is used by {$productUsage} product(s).",
            ], 422);
        }

        if ($derivedCount > 0) {
            return response()->json([
                'message' => "Cannot delete unit. It is the base unit for {$derivedCount} other unit(s).",
            ], 422);
        }

        $unit->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Unit deleted successfully']],
        ]);
    }
}
