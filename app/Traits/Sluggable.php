<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Trait for models that need auto-generated slugs
 */
trait Sluggable
{
    /**
     * Boot the trait
     */
    public static function bootSluggable(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            // Only regenerate slug if the source field changed and slug wasn't manually set
            $slugSource = $model->getSlugSource();
            if ($model->isDirty($slugSource) && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    /**
     * Get the field to generate slug from
     * Override in model to customize
     */
    protected function getSlugSource(): string
    {
        return $this->slugSource ?? 'name';
    }

    /**
     * Generate a unique slug
     */
    public function generateUniqueSlug(): string
    {
        $source = $this->getSlugSource();
        $slug = Str::slug($this->{$source});

        if (empty($slug)) {
            $slug = Str::random(8);
        }

        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug already exists
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }

    /**
     * Find by slug
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Find by slug or fail
     */
    public static function findBySlugOrFail(string $slug): static
    {
        return static::where('slug', $slug)->firstOrFail();
    }
}
