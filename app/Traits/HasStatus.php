<?php

namespace App\Traits;

use App\Models\Status;
use App\Models\StatusHistory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait HasStatus
{
    /**
     * Boot the trait.
     */
    public static function bootHasStatus(): void
    {
        static::creating(function ($model) {
            if (!$model->current_status_id) {
                $defaultStatus = Status::getDefault($model->getStatusCategoryType());
                if ($defaultStatus) {
                    $model->current_status_id = $defaultStatus->id;
                }
            }
        });

        static::created(function ($model) {
            if ($model->current_status_id) {
                $model->recordStatusChange($model->current_status_id, null, 'Initial status', true);
            }
        });
    }

    /**
     * Get the category type for status.
     * Override this method in your model if the class name doesn't match.
     */
    public function getStatusCategoryType(): string
    {
        return class_basename($this);
    }

    /**
     * Get the current status relationship.
     */
    public function currentStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'current_status_id');
    }

    /**
     * Get all status histories for this model.
     */
    public function statusHistories(): MorphMany
    {
        return $this->morphMany(StatusHistory::class, 'model')->latest();
    }

    /**
     * Set a new status by Status model.
     */
    public function setStatus(Status $status, ?string $reason = null, ?int $userId = null): self
    {
        $previousStatusId = $this->current_status_id;

        $this->current_status_id = $status->id;
        $this->save();

        $this->recordStatusChange($status->id, $previousStatusId, $reason, false, $userId);

        return $this;
    }

    /**
     * Change status by code.
     */
    public function changeStatus(string $code, ?string $reason = null): self
    {
        $status = Status::findByCode($code, $this->getStatusCategoryType());

        if (!$status) {
            throw new \InvalidArgumentException("Status '{$code}' not found for {$this->getStatusCategoryType()}");
        }

        if (!$this->canTransitionTo($status)) {
            throw new \InvalidArgumentException("Cannot transition to status '{$code}'");
        }

        return $this->setStatus($status, $reason);
    }

    /**
     * Check if the model can transition to a given status.
     */
    public function canTransitionTo(Status $status): bool
    {
        // By default, allow all transitions
        // Override this method in your model for custom transition rules
        $allowedTransitions = $this->getAllowedStatusTransitions();

        if (empty($allowedTransitions)) {
            return true;
        }

        $currentCode = $this->currentStatus?->code;

        if (!$currentCode || !isset($allowedTransitions[$currentCode])) {
            return true;
        }

        return in_array($status->code, $allowedTransitions[$currentCode]);
    }

    /**
     * Get allowed status transitions.
     * Override this method in your model for custom rules.
     */
    public function getAllowedStatusTransitions(): array
    {
        return [];
    }

    /**
     * Get available statuses for this model's category.
     */
    public function getAvailableStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        return Status::getForCategory($this->getStatusCategoryType());
    }

    /**
     * Get allowed next statuses based on current status.
     */
    public function getAllowedNextStatuses(): \Illuminate\Database\Eloquent\Collection
    {
        $transitions = $this->getAllowedStatusTransitions();
        $currentCode = $this->currentStatus?->code;

        if (empty($transitions) || !$currentCode || !isset($transitions[$currentCode])) {
            return $this->getAvailableStatuses();
        }

        $allowedCodes = $transitions[$currentCode];

        return Status::forCategory($this->getStatusCategoryType())
            ->active()
            ->whereIn('code', $allowedCodes)
            ->ordered()
            ->get();
    }

    /**
     * Record a status change in history.
     */
    protected function recordStatusChange(
        int $statusId,
        ?int $previousStatusId,
        ?string $reason = null,
        bool $isSystemChange = false,
        ?int $userId = null
    ): StatusHistory {
        return $this->statusHistories()->create([
            'status_id' => $statusId,
            'previous_status_id' => $previousStatusId,
            'reason' => $reason,
            'user_id' => $userId ?? Auth::id(),
            'is_system_change' => $isSystemChange,
        ]);
    }

    /**
     * Check if the model has a specific status code.
     */
    public function hasStatus(string $code): bool
    {
        return $this->currentStatus?->code === $code;
    }

    /**
     * Check if the model has any of the given status codes.
     */
    public function hasAnyStatus(array $codes): bool
    {
        return in_array($this->currentStatus?->code, $codes);
    }

    /**
     * Scope to filter by status code.
     */
    public function scopeWhereStatus($query, string $code)
    {
        return $query->whereHas('currentStatus', function ($q) use ($code) {
            $q->where('code', $code);
        });
    }

    /**
     * Scope to filter by multiple status codes.
     */
    public function scopeWhereStatusIn($query, array $codes)
    {
        return $query->whereHas('currentStatus', function ($q) use ($codes) {
            $q->whereIn('code', $codes);
        });
    }

    /**
     * Scope to exclude status codes.
     */
    public function scopeWhereStatusNotIn($query, array $codes)
    {
        return $query->whereHas('currentStatus', function ($q) use ($codes) {
            $q->whereNotIn('code', $codes);
        });
    }

    /**
     * Get status display name.
     */
    public function getStatusNameAttribute(): ?string
    {
        return $this->currentStatus?->name;
    }

    /**
     * Get status color.
     */
    public function getStatusColorAttribute(): ?string
    {
        return $this->currentStatus?->color;
    }
}
