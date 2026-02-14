<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $fillable = [
        'category_type',
        'code',
        'name',
        'color',
        'display_order',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get all status histories for this status.
     */
    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }

    /**
     * Scope to filter by category type.
     */
    public function scopeForCategory($query, string $categoryType)
    {
        return $query->where('category_type', $categoryType);
    }

    /**
     * Scope to filter active statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get default status for a category.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope to order by display order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    /**
     * Get default status for a category type.
     */
    public static function getDefault(string $categoryType): ?self
    {
        return static::forCategory($categoryType)
            ->active()
            ->default()
            ->first();
    }

    /**
     * Get statuses for a category type.
     */
    public static function getForCategory(string $categoryType): \Illuminate\Database\Eloquent\Collection
    {
        return static::forCategory($categoryType)
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Find a status by code and category type.
     */
    public static function findByCode(string $code, string $categoryType): ?self
    {
        return static::where('code', $code)
            ->where('category_type', $categoryType)
            ->first();
    }
}
