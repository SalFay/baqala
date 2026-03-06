<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Warranty extends BaseModel
{
    use SoftDeletes;

    const DURATION_DAYS = 'days';
    const DURATION_MONTHS = 'months';
    const DURATION_YEARS = 'years';

    protected $fillable = [
        'store_id',
        'name',
        'description',
        'duration',
        'duration_type',
        'terms',
        'coverage',
        'exclusions',
        'is_transferable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'duration' => 'integer',
        'is_transferable' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warranties')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function claims(): HasMany
    {
        return $this->hasMany(WarrantyClaim::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ==================== Helper Methods ====================

    /**
     * Get the total duration in days
     */
    public function getDurationInDays(): int
    {
        return match ($this->duration_type) {
            self::DURATION_DAYS => $this->duration,
            self::DURATION_MONTHS => $this->duration * 30,
            self::DURATION_YEARS => $this->duration * 365,
            default => $this->duration,
        };
    }

    /**
     * Calculate warranty end date from a start date
     */
    public function calculateEndDate(Carbon $startDate): Carbon
    {
        return match ($this->duration_type) {
            self::DURATION_DAYS => $startDate->copy()->addDays($this->duration),
            self::DURATION_MONTHS => $startDate->copy()->addMonths($this->duration),
            self::DURATION_YEARS => $startDate->copy()->addYears($this->duration),
            default => $startDate->copy()->addDays($this->duration),
        };
    }

    /**
     * Check if warranty is still valid for a given start date
     */
    public function isValidFromDate(Carbon $startDate): bool
    {
        return $this->calculateEndDate($startDate)->isFuture();
    }

    /**
     * Get remaining days from a start date
     */
    public function getRemainingDays(Carbon $startDate): int
    {
        $endDate = $this->calculateEndDate($startDate);

        if ($endDate->isPast()) {
            return 0;
        }

        return now()->diffInDays($endDate);
    }

    /**
     * Get display text for duration
     */
    public function getDurationDisplayAttribute(): string
    {
        $unit = match ($this->duration_type) {
            self::DURATION_DAYS => $this->duration === 1 ? 'Day' : 'Days',
            self::DURATION_MONTHS => $this->duration === 1 ? 'Month' : 'Months',
            self::DURATION_YEARS => $this->duration === 1 ? 'Year' : 'Years',
            default => '',
        };

        return "{$this->duration} {$unit}";
    }

    /**
     * Get all possible duration types
     */
    public static function getDurationTypes(): array
    {
        return [
            self::DURATION_DAYS => 'Days',
            self::DURATION_MONTHS => 'Months',
            self::DURATION_YEARS => 'Years',
        ];
    }
}
