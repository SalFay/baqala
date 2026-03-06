<?php

namespace App\Http\Controllers;

use App\Models\VariationTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VariationTemplateController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Variations/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = VariationTemplate::query();

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

        $templates = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'description' => $t->description,
                'attributes' => $t->attributes,
                'attributes_count' => count($t->attributes ?? []),
                'combinations_count' => count($t->generateCombinations()),
                'is_active' => $t->is_active,
                'sort_order' => $t->sort_order,
                'created_at' => $t->created_at,
            ]);

        return response()->json([
            'data' => $templates,
            'total' => $total,
        ]);
    }

    public function all(): JsonResponse
    {
        $templates = VariationTemplate::active()
            ->ordered()
            ->get(['id', 'name', 'attributes']);

        return response()->json(['data' => $templates]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:100',
            'attributes.*.values' => 'required|array|min:1',
            'attributes.*.values.*' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $template = VariationTemplate::create($validated);

        return response()->json([
            'data' => $template,
            'notifications' => [['type' => 'success', 'message' => 'Variation template created successfully']],
        ], 201);
    }

    public function update(Request $request, VariationTemplate $variationTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:500',
            'attributes' => 'nullable|array',
            'attributes.*.name' => 'required|string|max:100',
            'attributes.*.values' => 'required|array|min:1',
            'attributes.*.values.*' => 'required|string|max:100',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $variationTemplate->update($validated);

        return response()->json([
            'data' => $variationTemplate->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Variation template updated successfully']],
        ]);
    }

    public function destroy(VariationTemplate $variationTemplate): JsonResponse
    {
        // Check if template is in use
        $usageCount = $variationTemplate->productVariants()->count();

        if ($usageCount > 0) {
            return response()->json([
                'message' => "Cannot delete template. It is used by {$usageCount} product variant(s).",
            ], 422);
        }

        $variationTemplate->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Variation template deleted successfully']],
        ]);
    }

    public function generateCombinations(VariationTemplate $variationTemplate): JsonResponse
    {
        return response()->json([
            'combinations' => $variationTemplate->generateCombinations(),
        ]);
    }
}
