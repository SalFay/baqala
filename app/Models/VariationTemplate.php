<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VariationTemplate extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'attributes',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helpers
    public function getAttributeNames(): array
    {
        return collect($this->attributes ?? [])->pluck('name')->toArray();
    }

    public function getAttributeValues(string $attributeName): array
    {
        $attribute = collect($this->attributes ?? [])->firstWhere('name', $attributeName);
        return $attribute['values'] ?? [];
    }

    /**
     * Generate all possible variant combinations from template attributes.
     */
    public function generateCombinations(): array
    {
        $attributes = $this->attributes ?? [];

        if (empty($attributes)) {
            return [];
        }

        $combinations = [[]];

        foreach ($attributes as $attribute) {
            $newCombinations = [];
            foreach ($combinations as $combination) {
                foreach ($attribute['values'] as $value) {
                    $newCombinations[] = array_merge($combination, [
                        $attribute['name'] => $value
                    ]);
                }
            }
            $combinations = $newCombinations;
        }

        return $combinations;
    }
}
