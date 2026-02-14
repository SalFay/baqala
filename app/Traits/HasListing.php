<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

trait HasListing
{
    /**
     * Get paginated listing with filters, sorts, and search
     */
    protected function getListing(
        Request $request,
        string $modelClass,
        array $with = [],
        ?string $resource = null,
        array $options = []
    ): JsonResponse {
        $query = $modelClass::query();

        // Eager load relationships
        if (!empty($with)) {
            $query->with($with);
        }

        // With count
        if (!empty($options['withCount'])) {
            $query->withCount($options['withCount']);
        }

        // Pre-filter hook
        if (!empty($options['preFilter']) && is_callable($options['preFilter'])) {
            $options['preFilter']($query, $request);
        }

        // Apply search
        $this->applySearch($query, $request, $options['searchColumns'] ?? []);

        // Apply filters
        $this->applyFilters($query, $request, $options['filterColumns'] ?? []);

        // Apply soft delete filter
        $this->applySoftDeleteFilter($query, $request);

        // Post-filter hook
        if (!empty($options['postFilter']) && is_callable($options['postFilter'])) {
            $options['postFilter']($query, $request);
        }

        // Apply sorting
        $this->applySorting($query, $request, $options['defaultSort'] ?? 'created_at', $options['defaultSortDir'] ?? 'desc');

        // Check for export
        if ($request->boolean('export')) {
            return $this->exportData($query, $request, $resource);
        }

        // Paginate
        $perPage = min($request->input('per_page', 20), 100);
        $data = $query->paginate($perPage);

        // Transform with resource if provided
        if ($resource) {
            $items = $resource::collection($data->items());
        } else {
            $items = $data->items();
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'current_page' => $data->currentPage(),
            'per_page' => $data->perPage(),
            'total' => $data->total(),
            'last_page' => $data->lastPage(),
        ]);
    }

    /**
     * Apply search across multiple columns
     */
    protected function applySearch(Builder $query, Request $request, array $searchColumns): void
    {
        $search = $request->input('search') ?? $request->input('keyword');

        if (!$search || empty($searchColumns)) {
            return;
        }

        $search = strtolower($search);

        $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                if (str_contains($column, '.')) {
                    // Relationship column
                    [$relation, $field] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($subQ) use ($search, $field) {
                        $subQ->whereRaw("LOWER({$field}) LIKE ?", ["%{$search}%"]);
                    });
                } else {
                    $q->orWhereRaw("LOWER({$column}) LIKE ?", ["%{$search}%"]);
                }
            }
        });
    }

    /**
     * Apply column filters
     */
    protected function applyFilters(Builder $query, Request $request, array $filterColumns): void
    {
        foreach ($filterColumns as $column => $type) {
            $value = $request->input($column);

            if ($value === null || $value === '') {
                continue;
            }

            // Handle relationship filters
            if (str_contains($column, '.')) {
                [$relation, $field] = explode('.', $column, 2);
                $query->whereHas($relation, function ($q) use ($field, $value, $type) {
                    $this->applyFilterCondition($q, $field, $value, $type);
                });
                continue;
            }

            $this->applyFilterCondition($query, $column, $value, $type);
        }

        // Handle date range filters
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->input('to_date'));
        }
    }

    /**
     * Apply single filter condition
     */
    protected function applyFilterCondition(Builder $query, string $column, mixed $value, string $type): void
    {
        match ($type) {
            'exact' => $query->where($column, $value),
            'like' => $query->whereRaw("LOWER({$column}) LIKE ?", ['%' . strtolower($value) . '%']),
            'in' => $query->whereIn($column, is_array($value) ? $value : explode(',', $value)),
            'boolean' => $query->where($column, filter_var($value, FILTER_VALIDATE_BOOLEAN)),
            'date' => $query->whereDate($column, $value),
            'gte' => $query->where($column, '>=', $value),
            'lte' => $query->where($column, '<=', $value),
            default => $query->where($column, $value),
        };
    }

    /**
     * Apply soft delete filter
     */
    protected function applySoftDeleteFilter(Builder $query, Request $request): void
    {
        if ($request->boolean('with_trashed')) {
            $query->withTrashed();
        } elseif ($request->boolean('only_trashed')) {
            $query->onlyTrashed();
        }
    }

    /**
     * Apply sorting
     */
    protected function applySorting(Builder $query, Request $request, string $defaultSort, string $defaultDir): void
    {
        $sortBy = $request->input('sort_by', $defaultSort);
        $sortDir = $request->input('sort_direction', $defaultDir);

        // Validate direction
        $sortDir = in_array(strtolower($sortDir), ['asc', 'desc']) ? $sortDir : 'desc';

        // Handle relationship sorting
        if (str_contains($sortBy, '.')) {
            // For now, ignore relationship sorting (can be enhanced)
            return;
        }

        $query->orderBy($sortBy, $sortDir);
    }

    /**
     * Export data to JSON (can be extended for Excel/CSV)
     */
    protected function exportData(Builder $query, Request $request, ?string $resource): JsonResponse
    {
        $data = $query->limit(10000)->get();

        if ($resource) {
            $items = $resource::collection($data);
        } else {
            $items = $data;
        }

        return response()->json([
            'success' => true,
            'data' => $items,
            'export' => true,
            'count' => $data->count(),
        ]);
    }
}
