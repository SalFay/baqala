<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StatusHistory extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'status_id',
        'previous_status_id',
        'reason',
        'user_id',
        'is_system_change',
    ];

    protected $casts = [
        'is_system_change' => 'boolean',
    ];

    /**
     * Get the parent model (polymorphic).
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the status.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the previous status.
     */
    public function previousStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'previous_status_id');
    }

    /**
     * Get the user who changed the status.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get the most recent status change.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Scope to filter by user changes (not system).
     */
    public function scopeUserChanges($query)
    {
        return $query->where('is_system_change', false);
    }

    /**
     * Scope to filter by system changes.
     */
    public function scopeSystemChanges($query)
    {
        return $query->where('is_system_change', true);
    }
}
