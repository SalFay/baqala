<?php

namespace App\Http\Controllers;

use App\Models\DiscountRule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DiscountRuleController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/Discounts/Rules/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = DiscountRule::query();

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
            $query->orderBy('priority', 'desc')->orderBy('name');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $rules = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($rule) => [
                'id' => $rule->id,
                'name' => $rule->name,
                'description' => $rule->description,
                'discount_type' => $rule->discount_type,
                'discount_amount' => $rule->discount_amount,
                'discount_display' => $rule->discount_display,
                'applies_to' => $rule->applies_to,
                'applies_to_ids' => $rule->applies_to_ids,
                'conditions' => $rule->conditions,
                'priority' => $rule->priority,
                'is_stackable' => $rule->is_stackable,
                'max_uses' => $rule->max_uses,
                'current_uses' => $rule->current_uses,
                'starts_at' => $rule->starts_at?->format('Y-m-d H:i'),
                'ends_at' => $rule->ends_at?->format('Y-m-d H:i'),
                'is_valid' => $rule->isValid(),
                'is_active' => $rule->is_active,
                'created_at' => $rule->created_at,
            ]);

        return response()->json([
            'data' => $rules,
            'total' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_amount' => 'required|numeric|min:0.01',
            'applies_to' => 'required|in:all,category,brand,product,customer_group',
            'applies_to_ids' => 'nullable|array',
            'conditions' => 'nullable|array',
            'priority' => 'nullable|integer|min:0',
            'is_stackable' => 'boolean',
            'stop_further_rules' => 'boolean',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $rule = DiscountRule::create($validated);

        return response()->json([
            'data' => $rule,
            'notifications' => [['type' => 'success', 'message' => 'Discount rule created successfully']],
        ], 201);
    }

    public function update(Request $request, DiscountRule $discountRule): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'discount_type' => 'sometimes|in:fixed,percentage',
            'discount_amount' => 'sometimes|numeric|min:0.01',
            'applies_to' => 'sometimes|in:all,category,brand,product,customer_group',
            'applies_to_ids' => 'nullable|array',
            'conditions' => 'nullable|array',
            'priority' => 'nullable|integer|min:0',
            'is_stackable' => 'boolean',
            'stop_further_rules' => 'boolean',
            'max_uses' => 'nullable|integer|min:1',
            'max_uses_per_customer' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);

        $discountRule->update($validated);

        return response()->json([
            'data' => $discountRule->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Discount rule updated successfully']],
        ]);
    }

    public function destroy(DiscountRule $discountRule): JsonResponse
    {
        $discountRule->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Discount rule deleted successfully']],
        ]);
    }
}
