<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait for models that support drag-drop ordering
 */
trait SortableTrait
{
    /**
     * Boot the trait
     */
    public static function bootSortableTrait(): void
    {
        static::creating(function ($model) {
            if (is_null($model->sort_order)) {
                $model->sort_order = static::getNextSortOrder($model);
            }
        });
    }

    /**
     * Get the next sort order value
     */
    protected static function getNextSortOrder($model): int
    {
        $query = static::query();

        // If model has parent_id, scope to same parent
        if (isset($model->parent_id)) {
            $query->where('parent_id', $model->parent_id);
        }

        // If model has store_id, scope to same store
        if (isset($model->store_id)) {
            $query->where('store_id', $model->store_id);
        }

        return ($query->max('sort_order') ?? 0) + 1;
    }

    /**
     * Scope to order by sort_order
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order', 'asc');
    }

    /**
     * Move to specific position
     */
    public function moveTo(int $position): void
    {
        $oldPosition = $this->sort_order;

        if ($oldPosition === $position) {
            return;
        }

        $query = static::query();

        // Scope to same parent if applicable
        if (isset($this->parent_id)) {
            $query->where('parent_id', $this->parent_id);
        }

        if ($oldPosition < $position) {
            // Moving down
            $query->whereBetween('sort_order', [$oldPosition + 1, $position])
                  ->decrement('sort_order');
        } else {
            // Moving up
            $query->whereBetween('sort_order', [$position, $oldPosition - 1])
                  ->increment('sort_order');
        }

        $this->sort_order = $position;
        $this->saveQuietly();
    }

    /**
     * Move up one position
     */
    public function moveUp(): void
    {
        if ($this->sort_order > 1) {
            $this->moveTo($this->sort_order - 1);
        }
    }

    /**
     * Move down one position
     */
    public function moveDown(): void
    {
        $this->moveTo($this->sort_order + 1);
    }

    /**
     * Move to first position
     */
    public function moveToFirst(): void
    {
        $this->moveTo(1);
    }

    /**
     * Move to last position
     */
    public function moveToLast(): void
    {
        $query = static::query();

        if (isset($this->parent_id)) {
            $query->where('parent_id', $this->parent_id);
        }

        $maxOrder = $query->max('sort_order') ?? 0;
        $this->moveTo($maxOrder);
    }

    /**
     * Reorder multiple items
     */
    public static function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            static::where('id', $id)->update(['sort_order' => $index + 1]);
        }
    }
}
