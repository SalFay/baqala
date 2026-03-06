<?php

namespace App\Http\Controllers;

use App\Models\Modifier;
use App\Models\ModifierSet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ModifierSetController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Modifiers/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = ModifierSet::with('modifiers');

        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
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

        $sets = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($set) => [
                'id' => $set->id,
                'name' => $set->name,
                'description' => $set->description,
                'selection_type' => $set->selection_type,
                'is_required' => $set->is_required,
                'min_selections' => $set->min_selections,
                'max_selections' => $set->max_selections,
                'modifiers_count' => $set->modifiers->count(),
                'modifiers' => $set->modifiers->map(fn($m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'price_adjustment' => $m->price_adjustment,
                    'price_type' => $m->price_type,
                    'is_default' => $m->is_default,
                    'is_active' => $m->is_active,
                    'sort_order' => $m->sort_order,
                ]),
                'products_count' => $set->products()->count(),
                'is_active' => $set->is_active,
                'sort_order' => $set->sort_order,
                'created_at' => $set->created_at,
            ]);

        return response()->json([
            'data' => $sets,
            'total' => $total,
        ]);
    }

    public function all(): JsonResponse
    {
        $sets = ModifierSet::with('activeModifiers')
            ->active()
            ->ordered()
            ->get()
            ->map(fn($set) => [
                'id' => $set->id,
                'name' => $set->name,
                'selection_type' => $set->selection_type,
                'is_required' => $set->is_required,
                'min_selections' => $set->min_selections,
                'max_selections' => $set->max_selections,
                'modifiers' => $set->activeModifiers,
            ]);

        return response()->json(['data' => $sets]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'selection_type' => 'required|in:single,multiple',
            'is_required' => 'boolean',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'modifiers' => 'nullable|array',
            'modifiers.*.name' => 'required|string|max:255',
            'modifiers.*.price_adjustment' => 'nullable|numeric',
            'modifiers.*.price_type' => 'nullable|in:fixed,percentage',
            'modifiers.*.is_default' => 'boolean',
            'modifiers.*.is_active' => 'boolean',
            'modifiers.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $modifiers = $validated['modifiers'] ?? [];
        unset($validated['modifiers']);

        $set = ModifierSet::create($validated);

        // Create modifiers
        foreach ($modifiers as $index => $modifier) {
            $set->modifiers()->create([
                'name' => $modifier['name'],
                'price_adjustment' => $modifier['price_adjustment'] ?? 0,
                'price_type' => $modifier['price_type'] ?? 'fixed',
                'is_default' => $modifier['is_default'] ?? false,
                'is_active' => $modifier['is_active'] ?? true,
                'sort_order' => $modifier['sort_order'] ?? $index,
            ]);
        }

        return response()->json([
            'data' => $set->load('modifiers'),
            'notifications' => [['type' => 'success', 'message' => 'Modifier set created successfully']],
        ], 201);
    }

    public function update(Request $request, ModifierSet $modifierSet): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'selection_type' => 'sometimes|in:single,multiple',
            'is_required' => 'boolean',
            'min_selections' => 'nullable|integer|min:0',
            'max_selections' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'modifiers' => 'nullable|array',
            'modifiers.*.id' => 'nullable|integer',
            'modifiers.*.name' => 'required|string|max:255',
            'modifiers.*.price_adjustment' => 'nullable|numeric',
            'modifiers.*.price_type' => 'nullable|in:fixed,percentage',
            'modifiers.*.is_default' => 'boolean',
            'modifiers.*.is_active' => 'boolean',
            'modifiers.*.sort_order' => 'nullable|integer|min:0',
        ]);

        $modifiers = $validated['modifiers'] ?? null;
        unset($validated['modifiers']);

        $modifierSet->update($validated);

        // Update modifiers if provided
        if ($modifiers !== null) {
            $existingIds = [];

            foreach ($modifiers as $index => $modifierData) {
                if (isset($modifierData['id'])) {
                    // Update existing
                    $modifier = $modifierSet->modifiers()->find($modifierData['id']);
                    if ($modifier) {
                        $modifier->update([
                            'name' => $modifierData['name'],
                            'price_adjustment' => $modifierData['price_adjustment'] ?? 0,
                            'price_type' => $modifierData['price_type'] ?? 'fixed',
                            'is_default' => $modifierData['is_default'] ?? false,
                            'is_active' => $modifierData['is_active'] ?? true,
                            'sort_order' => $modifierData['sort_order'] ?? $index,
                        ]);
                        $existingIds[] = $modifier->id;
                    }
                } else {
                    // Create new
                    $newModifier = $modifierSet->modifiers()->create([
                        'name' => $modifierData['name'],
                        'price_adjustment' => $modifierData['price_adjustment'] ?? 0,
                        'price_type' => $modifierData['price_type'] ?? 'fixed',
                        'is_default' => $modifierData['is_default'] ?? false,
                        'is_active' => $modifierData['is_active'] ?? true,
                        'sort_order' => $modifierData['sort_order'] ?? $index,
                    ]);
                    $existingIds[] = $newModifier->id;
                }
            }

            // Delete modifiers not in the list
            $modifierSet->modifiers()
                ->whereNotIn('id', $existingIds)
                ->delete();
        }

        return response()->json([
            'data' => $modifierSet->fresh('modifiers'),
            'notifications' => [['type' => 'success', 'message' => 'Modifier set updated successfully']],
        ]);
    }

    public function destroy(ModifierSet $modifierSet): JsonResponse
    {
        $usageCount = $modifierSet->products()->count();

        if ($usageCount > 0) {
            return response()->json([
                'message' => "Cannot delete modifier set. It is used by {$usageCount} product(s).",
            ], 422);
        }

        $modifierSet->modifiers()->delete();
        $modifierSet->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Modifier set deleted successfully']],
        ]);
    }
}
