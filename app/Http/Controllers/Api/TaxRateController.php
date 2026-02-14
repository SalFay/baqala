<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TaxRate;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaxRateController extends Controller
{
    use ApiResponse;

    /**
     * List all tax rates
     */
    public function index(Request $request): JsonResponse
    {
        $query = TaxRate::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $taxRates = $query->orderBy('sort_order')->get();

        return $this->success($taxRates);
    }

    /**
     * Create tax rate
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'required|string|max:20|unique:tax_rates,code',
            'rate' => 'required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;
        $validated['is_default'] = $validated['is_default'] ?? false;
        $validated['sort_order'] = TaxRate::max('sort_order') + 1;

        // If setting as default, unset other defaults
        if ($validated['is_default']) {
            TaxRate::where('is_default', true)->update(['is_default' => false]);
        }

        $taxRate = TaxRate::create($validated);

        return $this->created($taxRate, 'Tax rate created successfully');
    }

    /**
     * Show tax rate
     */
    public function show(TaxRate $taxRate): JsonResponse
    {
        return $this->success($taxRate);
    }

    /**
     * Update tax rate
     */
    public function update(Request $request, TaxRate $taxRate): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'code' => 'sometimes|required|string|max:20|unique:tax_rates,code,' . $taxRate->id,
            'rate' => 'sometimes|required|numeric|min:0|max:100',
            'is_active' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'description' => 'nullable|string|max:500',
        ]);

        // If setting as default, unset other defaults
        if (!empty($validated['is_default']) && $validated['is_default']) {
            TaxRate::where('id', '!=', $taxRate->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);
        }

        $taxRate->update($validated);

        return $this->success($taxRate, 'Tax rate updated successfully');
    }

    /**
     * Delete tax rate
     */
    public function destroy(TaxRate $taxRate): JsonResponse
    {
        // Check if it's the default
        if ($taxRate->is_default) {
            return $this->error('Cannot delete the default tax rate', 422);
        }

        $taxRate->delete();

        return $this->success(null, 'Tax rate deleted successfully');
    }

    /**
     * Get default tax rate
     */
    public function default(): JsonResponse
    {
        $taxRate = TaxRate::where('is_default', true)->first();

        if (!$taxRate) {
            $taxRate = TaxRate::where('is_active', true)->first();
        }

        return $this->success($taxRate);
    }

    /**
     * Set as default
     */
    public function setDefault(TaxRate $taxRate): JsonResponse
    {
        TaxRate::where('is_default', true)->update(['is_default' => false]);
        $taxRate->update(['is_default' => true]);

        return $this->success($taxRate, 'Default tax rate updated');
    }

    /**
     * Reorder tax rates
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:tax_rates,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            TaxRate::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return $this->success(null, 'Tax rates reordered successfully');
    }
}
