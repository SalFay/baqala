<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessTypeController extends Controller
{
    /**
     * Display listing page or return JSON for DataGridTable.
     */
    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return $this->listing($request);
        }

        return Inertia::render('Settings/BusinessTypes/Index');
    }

    /**
     * Server-side listing for DataGridTable.
     */
    public function listing(Request $request): JsonResponse
    {
        $query = BusinessType::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Soft deleted filter
        if ($request->boolean('soft_deleted')) {
            $query->onlyTrashed();
        }

        // Sorting
        if ($request->filled('sort') && is_array($request->sort)) {
            foreach ($request->sort as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->ordered();
        }

        // Pagination
        $total = $query->count();
        $page = $request->input('current', 1);
        $pageSize = $request->input('pageSize', 20);

        $data = $query->skip(($page - 1) * $pageSize)
            ->take($pageSize)
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'name_ar' => $type->name_ar,
                'slug' => $type->slug,
                'icon' => $type->icon,
                'description' => $type->description,
                'default_attributes' => $type->default_attributes,
                'tax_config' => $type->tax_config,
                'receipt_config' => $type->receipt_config,
                'settings' => $type->settings,
                'is_active' => $type->is_active,
                'sort_order' => $type->sort_order,
                'has_seeder' => $type->hasSeeder(),
                'stores_count' => $type->stores()->count(),
                'created_at' => $type->created_at?->format('Y-m-d H:i'),
            ]);

        return response()->json([
            'data' => $data,
            'total' => $total,
        ]);
    }

    /**
     * List all active business types (for dropdowns).
     */
    public function all(): JsonResponse
    {
        $businessTypes = BusinessType::active()
            ->ordered()
            ->get()
            ->map(fn($type) => [
                'id' => $type->id,
                'name' => $type->name,
                'name_ar' => $type->name_ar,
                'slug' => $type->slug,
                'icon' => $type->icon,
                'description' => $type->description,
                'default_attributes' => $type->default_attributes,
                'tax_config' => $type->tax_config,
                'receipt_config' => $type->receipt_config,
                'settings' => $type->settings,
                'has_seeder' => $type->hasSeeder(),
            ]);

        return response()->json(['data' => $businessTypes]);
    }

    /**
     * Store a new business type.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'default_attributes' => 'nullable|array',
            'tax_config' => 'nullable|array',
            'receipt_config' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $businessType = BusinessType::create($validated);

        return response()->json([
            'data' => $businessType,
            'message' => 'Business type created successfully',
        ], 201);
    }

    /**
     * Update a business type.
     */
    public function update(Request $request, BusinessType $businessType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'default_attributes' => 'nullable|array',
            'tax_config' => 'nullable|array',
            'receipt_config' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $businessType->update($validated);

        return response()->json([
            'data' => $businessType,
            'message' => 'Business type updated successfully',
        ]);
    }

    /**
     * Delete a business type.
     */
    public function destroy(BusinessType $businessType): JsonResponse
    {
        // Check if any stores are using this business type
        if ($businessType->stores()->exists()) {
            return response()->json([
                'message' => 'Cannot delete business type that is assigned to stores',
            ], 422);
        }

        $businessType->delete();

        return response()->json([
            'message' => 'Business type deleted successfully',
        ]);
    }

    /**
     * Get the current store's business type.
     */
    public function current(): JsonResponse
    {
        $store = Store::first();
        $businessType = $store?->businessType;

        return response()->json([
            'data' => $businessType ? [
                'id' => $businessType->id,
                'name' => $businessType->name,
                'name_ar' => $businessType->name_ar,
                'slug' => $businessType->slug,
                'icon' => $businessType->icon,
                'description' => $businessType->description,
            ] : null,
        ]);
    }

    /**
     * Preview products from a business type seeder without importing.
     */
    public function preview(BusinessType $businessType): JsonResponse
    {
        if (!$businessType->hasSeeder()) {
            return response()->json([
                'error' => 'No seeder available for this business type',
            ], 400);
        }

        $seederClass = $businessType->getSeederClass();
        $seeder = new $seederClass();
        $previewData = $seeder->getPreviewData();

        return response()->json(['data' => $previewData]);
    }

    /**
     * Apply a business type to the store and optionally import products.
     */
    public function apply(Request $request, BusinessType $businessType): JsonResponse
    {
        $validated = $request->validate([
            'import_products' => 'boolean',
            'clear_existing' => 'boolean',
        ]);

        $store = Store::first();

        if (!$store) {
            return response()->json(['error' => 'No store found'], 400);
        }

        // Update store's business type
        $store->update(['business_type_id' => $businessType->id]);

        $importedCount = 0;

        // Import products if requested
        if ($validated['import_products'] ?? false) {
            if ($businessType->hasSeeder()) {
                $seederClass = $businessType->getSeederClass();

                // Clear existing products if requested
                if ($validated['clear_existing'] ?? false) {
                    // Delete products and related data
                    $store->products()->delete();
                }

                // Run the seeder
                Artisan::call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true,
                ]);

                // Get count of imported products
                $importedCount = $store->products()->count();
            }
        }

        return response()->json([
            'message' => 'Business type applied successfully',
            'data' => [
                'business_type' => [
                    'id' => $businessType->id,
                    'name' => $businessType->name,
                    'slug' => $businessType->slug,
                ],
                'products_imported' => $importedCount,
            ],
        ]);
    }

    /**
     * Seed all business types (without products).
     */
    public function seedTypes(): JsonResponse
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\BusinessType\\BusinessTypeSeeder',
            '--force' => true,
        ]);

        $count = BusinessType::count();

        return response()->json([
            'message' => "Seeded {$count} business types successfully",
            'data' => ['count' => $count],
        ]);
    }
}
