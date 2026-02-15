<?php

namespace App\Http\Controllers;

use App\Models\BusinessType;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class BusinessTypeController extends Controller
{
    /**
     * List all available business types.
     */
    public function index(): JsonResponse
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
