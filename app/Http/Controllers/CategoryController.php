<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\Category\StoreCategoryRequest;
use App\Http\Requests\Api\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Traits\HasListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
{
    use HasListing;

    public function index(Request $request): Response|JsonResponse
    {
        if ($request->wantsJson()) {
            return $this->listing($request);
        }

        // Get categories with hierarchy for tree view
        $categories = Category::with(['children' => function ($q) {
            $q->with('children')->withCount('products');
        }])
            ->withCount('products')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Index', [
            'categories' => CategoryResource::collection($categories),
        ]);
    }

    public function listing(Request $request): JsonResponse
    {
        return $this->getListing(
            $request,
            Category::class,
            resource: CategoryResource::class,
            options: [
                'searchColumns' => ['name'],
                'filterColumns' => [
                    'parent_id' => 'exact',
                    'is_active' => 'exact',
                ],
                'withCount' => ['products'],
                'defaultSort' => 'sort_order',
                'defaultSortDir' => 'asc',
            ]
        );
    }

    public function all(): JsonResponse
    {
        $categories = Category::withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse|JsonResponse
    {
        $category = Category::create($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new CategoryResource($category),
                'notifications' => [['type' => 'success', 'message' => 'Category created successfully']],
            ], 201);
        }

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse|JsonResponse
    {
        $category->update($request->validated());

        if ($request->wantsJson()) {
            return response()->json([
                'data' => new CategoryResource($category->fresh()),
                'notifications' => [['type' => 'success', 'message' => 'Category updated successfully']],
            ]);
        }

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse|JsonResponse
    {
        // Move children to parent (or make them root)
        Category::where('parent_id', $category->id)->update(['parent_id' => $category->parent_id]);

        $category->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'notifications' => [['type' => 'success', 'message' => 'Category deleted successfully']],
            ]);
        }

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
