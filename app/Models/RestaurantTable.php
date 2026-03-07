<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantTable extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'location_id',
        'name',
        'capacity',
        'status',
        'current_order_id',
        'section',
        'floor',
        'position_x',
        'position_y',
        'shape',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'position_x' => 'integer',
        'position_y' => 'integer',
        'is_active' => 'boolean',
    ];

    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_RESERVED = 'reserved';
    const STATUS_MAINTENANCE = 'maintenance';

    // Relationships

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function currentOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'current_order_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(TableReservation::class, 'table_id');
    }

    // Scopes

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', self::STATUS_OCCUPIED);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeInSection($query, string $section)
    {
        return $query->where('section', $section);
    }

    public function scopeOnFloor($query, string $floor)
    {
        return $query->where('floor', $floor);
    }

    public function scopeWithCapacity($query, int $minCapacity)
    {
        return $query->where('capacity', '>=', $minCapacity);
    }

    // Methods

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isOccupied(): bool
    {
        return $this->status === self::STATUS_OCCUPIED;
    }

    public function occupy(Order $order): self
    {
        $this->update([
            'status' => self::STATUS_OCCUPIED,
            'current_order_id' => $order->id,
        ]);
        return $this;
    }

    public function release(): self
    {
        $this->update([
            'status' => self::STATUS_AVAILABLE,
            'current_order_id' => null,
        ]);
        return $this;
    }

    public function reserve(): self
    {
        $this->update(['status' => self::STATUS_RESERVED]);
        return $this;
    }

    public function setMaintenance(): self
    {
        $this->update([
            'status' => self::STATUS_MAINTENANCE,
            'current_order_id' => null,
        ]);
        return $this;
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_AVAILABLE => 'green',
            self::STATUS_OCCUPIED => 'red',
            self::STATUS_RESERVED => 'orange',
            self::STATUS_MAINTENANCE => 'default',
            default => 'default',
        };
    }

    /**
     * Check if table has upcoming reservations
     */
    public function hasUpcomingReservations(): bool
    {
        return $this->reservations()
            ->where('reservation_date', '>=', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    /**
     * Get today's reservations
     */
    public function getTodayReservations()
    {
        return $this->reservations()
            ->where('reservation_date', today())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('start_time')
            ->get();
    }
}
