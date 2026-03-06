<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomFieldController extends Controller
{
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        return Inertia::render('Settings/CustomFields/Index');
    }

    public function listing(Request $request): JsonResponse
    {
        $query = CustomField::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('label', 'like', "%{$request->search}%");
            });
        }

        if ($request->entity_type) {
            $query->where('entity_type', $request->entity_type);
        }

        if ($request->soft_deleted) {
            $query->onlyTrashed();
        }

        if ($request->sort && count($request->sort) > 0) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('entity_type')->orderBy('sort_order')->orderBy('label');
        }

        $total = $query->count();
        $page = $request->current ?? 1;
        $pageSize = $request->pageSize ?? 20;

        $fields = $query
            ->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($field) => [
                'id' => $field->id,
                'entity_type' => $field->entity_type,
                'name' => $field->name,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'options' => $field->options,
                'default_value' => $field->default_value,
                'is_required' => $field->is_required,
                'is_searchable' => $field->is_searchable,
                'show_in_list' => $field->show_in_list,
                'show_in_pos' => $field->show_in_pos,
                'help_text' => $field->help_text,
                'values_count' => $field->values()->count(),
                'is_active' => $field->is_active,
                'sort_order' => $field->sort_order,
                'created_at' => $field->created_at,
            ]);

        return response()->json([
            'data' => $fields,
            'total' => $total,
        ]);
    }

    public function forEntity(string $entityType): JsonResponse
    {
        $fields = CustomField::active()
            ->forEntity($entityType)
            ->ordered()
            ->get()
            ->map(fn($field) => [
                'id' => $field->id,
                'name' => $field->name,
                'label' => $field->label,
                'field_type' => $field->field_type,
                'options' => $field->options,
                'default_value' => $field->default_value,
                'is_required' => $field->is_required,
                'help_text' => $field->help_text,
                'show_in_pos' => $field->show_in_pos,
            ]);

        return response()->json(['data' => $fields]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'entity_type' => 'required|in:product,customer,order,vendor',
            'name' => 'required|string|max:50|regex:/^[a-z][a-z0-9_]*$/',
            'label' => 'required|string|max:255',
            'field_type' => 'required|in:text,textarea,number,select,multiselect,date,datetime,boolean,url,email',
            'options' => 'nullable|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
            'default_value' => 'nullable|string|max:255',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_pos' => 'boolean',
            'validation_rules' => 'nullable|string|max:500',
            'help_text' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Check for duplicate name for same entity type
        $exists = CustomField::where('entity_type', $validated['entity_type'])
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => "A field with name '{$validated['name']}' already exists for this entity type.",
            ], 422);
        }

        $field = CustomField::create($validated);

        return response()->json([
            'data' => $field,
            'notifications' => [['type' => 'success', 'message' => 'Custom field created successfully']],
        ], 201);
    }

    public function update(Request $request, CustomField $customField): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'sometimes|string|max:255',
            'options' => 'nullable|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
            'default_value' => 'nullable|string|max:255',
            'is_required' => 'boolean',
            'is_searchable' => 'boolean',
            'show_in_list' => 'boolean',
            'show_in_pos' => 'boolean',
            'validation_rules' => 'nullable|string|max:500',
            'help_text' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        // Don't allow changing name, entity_type, or field_type after creation
        // as it would break existing values

        $customField->update($validated);

        return response()->json([
            'data' => $customField->fresh(),
            'notifications' => [['type' => 'success', 'message' => 'Custom field updated successfully']],
        ]);
    }

    public function destroy(CustomField $customField): JsonResponse
    {
        $valuesCount = $customField->values()->count();

        if ($valuesCount > 0) {
            return response()->json([
                'message' => "Cannot delete field. It has {$valuesCount} value(s) stored. Deactivate it instead.",
            ], 422);
        }

        $customField->delete();

        return response()->json([
            'notifications' => [['type' => 'success', 'message' => 'Custom field deleted successfully']],
        ]);
    }

    public function getMetadata(): JsonResponse
    {
        return response()->json([
            'entity_types' => CustomField::getEntityTypes(),
            'field_types' => CustomField::getFieldTypes(),
        ]);
    }
}
