<?php

namespace App\Traits;

use App\Models\Store;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trait for models that belong to a store (multi-store support)
 */
trait HasStore
{
    /**
     * Boot the trait - automatically set store_id on creating
     */
    public static function bootHasStore(): void
    {
        static::creating(function ($model) {
            if (!$model->store_id && auth()->check()) {
                $model->store_id = auth()->user()->current_store_id ?? auth()->user()->store_id;
            }
        });
    }

    /**
     * Get the store that owns this model
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Scope query to specific store
     */
    public function scopeForStore(Builder $query, ?int $storeId = null): Builder
    {
        $storeId = $storeId ?? auth()->user()?->current_store_id ?? auth()->user()?->store_id;

        return $query->where('store_id', $storeId);
    }

    /**
     * Scope query to current user's store
     */
    public function scopeCurrentStore(Builder $query): Builder
    {
        return $this->scopeForStore($query);
    }

    /**
     * Scope query to include global (null store_id) and specific store
     */
    public function scopeForStoreOrGlobal(Builder $query, ?int $storeId = null): Builder
    {
        $storeId = $storeId ?? auth()->user()?->current_store_id ?? auth()->user()?->store_id;

        return $query->where(function ($q) use ($storeId) {
            $q->whereNull('store_id')
              ->orWhere('store_id', $storeId);
        });
    }
}
