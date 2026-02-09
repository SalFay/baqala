<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models that support query filtering
 */
trait Filterable
{
    /**
     * Searchable columns for this model
     * Override in model to customize
     */
    protected function getSearchableColumns(): array
    {
        return $this->searchable ?? ['name'];
    }

    /**
     * Apply filters to query
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function ($q, $search) {
                $this->applySearch($q, $search);
            })
            ->when($filters['status'] ?? null, fn($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['is_active'] ?? null, fn($q, $isActive) =>
                $q->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN))
            )
            ->when($filters['category_id'] ?? null, fn($q, $categoryId) =>
                $q->where('category_id', $categoryId)
            )
            ->when($filters['store_id'] ?? null, fn($q, $storeId) =>
                $q->where('store_id', $storeId)
            )
            ->when($filters['from_date'] ?? null, fn($q, $date) =>
                $q->whereDate('created_at', '>=', $date)
            )
            ->when($filters['to_date'] ?? null, fn($q, $date) =>
                $q->whereDate('created_at', '<=', $date)
            )
            ->when($filters['date'] ?? null, fn($q, $date) =>
                $q->whereDate('created_at', $date)
            )
            ->when($filters['sort_by'] ?? null, function ($q, $sortBy) use ($filters) {
                $direction = $filters['sort_direction'] ?? 'asc';
                $q->orderBy($sortBy, $direction);
            }, function ($q) {
                $q->latest();
            });
    }

    /**
     * Apply search to query
     */
    protected function applySearch(Builder $query, string $search): void
    {
        $columns = $this->getSearchableColumns();

        $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Scope for active records
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for inactive records
     */
    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope for date range
     */
    public function scopeDateRange(Builder $query, ?string $from = null, ?string $to = null): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
    }
}
