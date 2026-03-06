<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends BaseModel
{
    use SoftDeletes;

    const ENTITY_PRODUCT = 'product';
    const ENTITY_CUSTOMER = 'customer';
    const ENTITY_ORDER = 'order';
    const ENTITY_VENDOR = 'vendor';

    const TYPE_TEXT = 'text';
    const TYPE_TEXTAREA = 'textarea';
    const TYPE_NUMBER = 'number';
    const TYPE_SELECT = 'select';
    const TYPE_MULTISELECT = 'multiselect';
    const TYPE_DATE = 'date';
    const TYPE_DATETIME = 'datetime';
    const TYPE_BOOLEAN = 'boolean';
    const TYPE_URL = 'url';
    const TYPE_EMAIL = 'email';

    protected $fillable = [
        'store_id',
        'entity_type',
        'name',
        'label',
        'field_type',
        'options',
        'default_value',
        'is_required',
        'is_searchable',
        'show_in_list',
        'show_in_pos',
        'validation_rules',
        'help_text',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_searchable' => 'boolean',
        'show_in_list' => 'boolean',
        'show_in_pos' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ==================== Relationships ====================

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomFieldValue::class);
    }

    // ==================== Scopes ====================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('label');
    }

    public function scopeForEntity($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeForProduct($query)
    {
        return $query->forEntity(self::ENTITY_PRODUCT);
    }

    public function scopeForCustomer($query)
    {
        return $query->forEntity(self::ENTITY_CUSTOMER);
    }

    public function scopeSearchable($query)
    {
        return $query->where('is_searchable', true);
    }

    public function scopeShowInList($query)
    {
        return $query->where('show_in_list', true);
    }

    public function scopeShowInPos($query)
    {
        return $query->where('show_in_pos', true);
    }

    // ==================== Helper Methods ====================

    /**
     * Check if this is a select-type field
     */
    public function isSelectType(): bool
    {
        return in_array($this->field_type, [self::TYPE_SELECT, self::TYPE_MULTISELECT]);
    }

    /**
     * Get options as key-value pairs
     */
    public function getOptionsArray(): array
    {
        if (!$this->options) {
            return [];
        }

        $result = [];
        foreach ($this->options as $option) {
            $result[$option['value']] = $option['label'];
        }
        return $result;
    }

    /**
     * Validate a value against this field's rules
     */
    public function validateValue($value): array
    {
        $errors = [];

        // Required check
        if ($this->is_required && ($value === null || $value === '')) {
            $errors[] = "{$this->label} is required";
            return $errors;
        }

        if ($value === null || $value === '') {
            return $errors;
        }

        // Type-specific validation
        switch ($this->field_type) {
            case self::TYPE_NUMBER:
                if (!is_numeric($value)) {
                    $errors[] = "{$this->label} must be a number";
                }
                break;

            case self::TYPE_EMAIL:
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[] = "{$this->label} must be a valid email";
                }
                break;

            case self::TYPE_URL:
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    $errors[] = "{$this->label} must be a valid URL";
                }
                break;

            case self::TYPE_SELECT:
                $validValues = array_column($this->options ?? [], 'value');
                if (!in_array($value, $validValues)) {
                    $errors[] = "{$this->label} has an invalid selection";
                }
                break;

            case self::TYPE_MULTISELECT:
                $validValues = array_column($this->options ?? [], 'value');
                $selectedValues = is_array($value) ? $value : [$value];
                foreach ($selectedValues as $val) {
                    if (!in_array($val, $validValues)) {
                        $errors[] = "{$this->label} has an invalid selection";
                        break;
                    }
                }
                break;

            case self::TYPE_DATE:
            case self::TYPE_DATETIME:
                if (strtotime($value) === false) {
                    $errors[] = "{$this->label} must be a valid date";
                }
                break;
        }

        return $errors;
    }

    /**
     * Format a value for display
     */
    public function formatValue($value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        switch ($this->field_type) {
            case self::TYPE_BOOLEAN:
                return $value ? 'Yes' : 'No';

            case self::TYPE_SELECT:
                $options = $this->getOptionsArray();
                return $options[$value] ?? $value;

            case self::TYPE_MULTISELECT:
                $options = $this->getOptionsArray();
                $values = is_array($value) ? $value : json_decode($value, true) ?? [];
                return implode(', ', array_map(fn($v) => $options[$v] ?? $v, $values));

            case self::TYPE_DATE:
                return date('Y-m-d', strtotime($value));

            case self::TYPE_DATETIME:
                return date('Y-m-d H:i', strtotime($value));

            default:
                return (string) $value;
        }
    }

    /**
     * Get Laravel validation rules for this field
     */
    public function getValidationRules(): array
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        switch ($this->field_type) {
            case self::TYPE_TEXT:
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;

            case self::TYPE_TEXTAREA:
                $rules[] = 'string';
                $rules[] = 'max:5000';
                break;

            case self::TYPE_NUMBER:
                $rules[] = 'numeric';
                break;

            case self::TYPE_EMAIL:
                $rules[] = 'email';
                break;

            case self::TYPE_URL:
                $rules[] = 'url';
                break;

            case self::TYPE_DATE:
                $rules[] = 'date';
                break;

            case self::TYPE_DATETIME:
                $rules[] = 'date';
                break;

            case self::TYPE_BOOLEAN:
                $rules[] = 'boolean';
                break;

            case self::TYPE_SELECT:
                $validValues = array_column($this->options ?? [], 'value');
                $rules[] = 'in:' . implode(',', $validValues);
                break;

            case self::TYPE_MULTISELECT:
                $rules[] = 'array';
                break;
        }

        // Add custom validation rules if specified
        if ($this->validation_rules) {
            $customRules = explode('|', $this->validation_rules);
            $rules = array_merge($rules, $customRules);
        }

        return $rules;
    }

    /**
     * Get all entity types
     */
    public static function getEntityTypes(): array
    {
        return [
            self::ENTITY_PRODUCT => 'Product',
            self::ENTITY_CUSTOMER => 'Customer',
            self::ENTITY_ORDER => 'Order',
            self::ENTITY_VENDOR => 'Vendor',
        ];
    }

    /**
     * Get all field types
     */
    public static function getFieldTypes(): array
    {
        return [
            self::TYPE_TEXT => 'Text',
            self::TYPE_TEXTAREA => 'Text Area',
            self::TYPE_NUMBER => 'Number',
            self::TYPE_SELECT => 'Dropdown',
            self::TYPE_MULTISELECT => 'Multi-Select',
            self::TYPE_DATE => 'Date',
            self::TYPE_DATETIME => 'Date & Time',
            self::TYPE_BOOLEAN => 'Yes/No',
            self::TYPE_URL => 'URL',
            self::TYPE_EMAIL => 'Email',
        ];
    }
}
