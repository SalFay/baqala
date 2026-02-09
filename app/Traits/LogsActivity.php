<?php

namespace App\Traits;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as SpatieLogsActivity;

/**
 * Trait for models that log activity changes
 */
trait LogsActivity
{
    use SpatieLogsActivity;

    /**
     * Get the activity log options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLogAttributes())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(fn(string $eventName) => $this->getActivityDescription($eventName));
    }

    /**
     * Get attributes to log
     * Override in model to customize
     */
    protected function getLogAttributes(): array
    {
        return $this->logAttributes ?? ['*'];
    }

    /**
     * Get the log name
     * Override in model to customize
     */
    protected function getLogName(): string
    {
        return $this->logName ?? strtolower(class_basename($this));
    }

    /**
     * Get activity description
     */
    protected function getActivityDescription(string $eventName): string
    {
        $modelName = class_basename($this);

        return match($eventName) {
            'created' => "{$modelName} was created",
            'updated' => "{$modelName} was updated",
            'deleted' => "{$modelName} was deleted",
            default => "{$modelName} was {$eventName}",
        };
    }
}
