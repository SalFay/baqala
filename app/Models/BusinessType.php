<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BusinessType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'icon',
        'description',
        'default_attributes',
        'tax_config',
        'receipt_config',
        'settings',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_attributes' => 'array',
        'tax_config' => 'array',
        'receipt_config' => 'array',
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the seeder class name for this business type.
     */
    public function getSeederClass(): string
    {
        $className = Str::studly($this->slug) . 'Seeder';
        return "Database\\Seeders\\BusinessType\\{$className}";
    }

    /**
     * Check if the seeder class exists.
     */
    public function hasSeeder(): bool
    {
        return class_exists($this->getSeederClass());
    }

    /**
     * Get product attributes specific to this business type.
     */
    public function getProductAttributes(): array
    {
        return $this->default_attributes['product_attributes'] ?? [];
    }

    /**
     * Get category structure for this business type.
     */
    public function getCategoryStructure(): array
    {
        return $this->default_attributes['categories'] ?? [];
    }
}
