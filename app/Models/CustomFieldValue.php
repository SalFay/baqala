<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomFieldValue extends BaseModel
{
    protected $fillable = [
        'custom_field_id',
        'entity_type',
        'entity_id',
        'value',
    ];

    // ==================== Relationships ====================

    public function customField(): BelongsTo
    {
        return $this->belongsTo(CustomField::class);
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    // ==================== Helper Methods ====================

    /**
     * Get the typed value based on field type
     */
    public function getTypedValue()
    {
        if ($this->value === null) {
            return null;
        }

        $field = $this->customField;
        if (!$field) {
            return $this->value;
        }

        switch ($field->field_type) {
            case CustomField::TYPE_NUMBER:
                return is_numeric($this->value) ? (float) $this->value : null;

            case CustomField::TYPE_BOOLEAN:
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);

            case CustomField::TYPE_MULTISELECT:
                $decoded = json_decode($this->value, true);
                return is_array($decoded) ? $decoded : [$this->value];

            case CustomField::TYPE_DATE:
            case CustomField::TYPE_DATETIME:
                return $this->value ? \Carbon\Carbon::parse($this->value) : null;

            default:
                return $this->value;
        }
    }

    /**
     * Get formatted value for display
     */
    public function getFormattedValue(): string
    {
        $field = $this->customField;
        if (!$field) {
            return (string) $this->value;
        }

        return $field->formatValue($this->value);
    }

    /**
     * Set value with proper encoding for complex types
     */
    public function setTypedValue($value): self
    {
        $field = $this->customField;

        if ($field && $field->field_type === CustomField::TYPE_MULTISELECT && is_array($value)) {
            $this->value = json_encode($value);
        } elseif ($field && $field->field_type === CustomField::TYPE_BOOLEAN) {
            $this->value = $value ? '1' : '0';
        } else {
            $this->value = $value;
        }

        return $this;
    }
}
