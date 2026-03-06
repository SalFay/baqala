<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModifierSet extends BaseModel
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'selection_type',
        'is_required',
        'min_selections',
        'max_selections',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
    ];

    // Relationships
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(Modifier::class)->orderBy('sort_order');
    }

    public function activeModifiers(): HasMany
    {
        return $this->hasMany(Modifier::class)->where('is_active', true)->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_modifier_sets')
            ->withPivot('sort_order')
            ->withTimestamps();
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
    public function isSingleSelection(): bool
    {
        return $this->selection_type === 'single';
    }

    public function isMultipleSelection(): bool
    {
        return $this->selection_type === 'multiple';
    }

    public function getDefaultModifiers()
    {
        return $this->modifiers()->where('is_default', true)->get();
    }

    /**
     * Validate if selected modifiers meet the requirements.
     */
    public function validateSelection(array $selectedModifierIds): bool
    {
        $count = count($selectedModifierIds);

        if ($this->is_required && $count < max(1, $this->min_selections)) {
            return false;
        }

        if ($this->min_selections > 0 && $count < $this->min_selections) {
            return false;
        }

        if ($this->max_selections !== null && $count > $this->max_selections) {
            return false;
        }

        if ($this->isSingleSelection() && $count > 1) {
            return false;
        }

        return true;
    }
}
