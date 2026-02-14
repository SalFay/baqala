<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    public function index(): Response
    {
        // Get categories with hierarchy
        $categories = Category::with(['children' => function ($q) {
            $q->with('children')->withCount('products');
        }])
            ->withCount('products')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Also get flat list for select dropdowns
        $flatCategories = Category::withCount('products')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => [
                'id' => $cat->id,
                'name' => $cat->name,
                'parent_id' => $cat->parent_id,
                'products_count' => $cat->products_count,
                'is_active' => $cat->is_active,
            ]);

        return Inertia::render('Categories/Index', [
            'categories' => $this->formatCategoryTree($categories),
            'flatCategories' => $flatCategories,
        ]);
    }

    private function formatCategoryTree($categories)
    {
        return $categories->map(fn($cat) => [
            'id' => $cat->id,
            'name' => $cat->name,
            'parent_id' => $cat->parent_id,
            'description' => $cat->description,
            'products_count' => $cat->products_count,
            'is_active' => $cat->is_active,
            'children' => $cat->children->count() > 0 ? $this->formatCategoryTree($cat->children) : [],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        Category::create($validated);

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // Prevent setting parent to self or own children
        if ($validated['parent_id']) {
            $descendantIds = $this->getDescendantIds($category);
            if (in_array($validated['parent_id'], $descendantIds) || $validated['parent_id'] == $category->id) {
                return redirect()->back()->withErrors(['parent_id' => 'Cannot set parent to self or descendant.']);
            }
        }

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    private function getDescendantIds(Category $category): array
    {
        $ids = [];
        foreach ($category->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }
        return $ids;
    }

    public function destroy(Category $category): RedirectResponse
    {
        // Move children to parent (or make them root)
        Category::where('parent_id', $category->id)->update(['parent_id' => $category->parent_id]);

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
