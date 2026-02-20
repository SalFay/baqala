import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Modal,
  Button,
  Select,
  Input,
  InputNumber,
  DatePicker,
  Space,
  Flex,
  Typography,
  theme,
  Tooltip,
} from 'antd';
import { DeleteOutlined, PlusOutlined, FilterOutlined, InfoCircleOutlined } from '@ant-design/icons';
import dayjs from 'dayjs';

const { Text } = Typography;
const { Option } = Select;
const { RangePicker } = DatePicker;
const { useToken } = theme;

const EMPTY_CONDITION = { field: null, operator: 'is', value: null };
const DATE_FORMAT = 'YYYY-MM-DD';
const DISPLAY_DATE_FORMAT = 'MM-DD-YYYY';

// Operator options by field type (SparkCRM pattern)
const OPERATOR_OPTIONS = {
  date: [
    { value: 'is', label: 'Is' },
    { value: 'between', label: 'Between' },
    { value: 'after', label: 'After' },
    { value: 'before', label: 'Before' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  number: [
    { value: 'is', label: 'Equal' },
    { value: 'between', label: 'Between' },
    { value: 'greater_than', label: 'Greater than' },
    { value: 'greater_than_or_equal', label: 'Greater than or equal' },
    { value: 'less_than', label: 'Less than' },
    { value: 'less_than_or_equal', label: 'Less than or equal' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  text: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'contains', label: 'Contains' },
    { value: 'does not contain', label: 'Does not contain' },
    { value: 'starts with', label: 'Starts with' },
    { value: 'ends with', label: 'Ends with' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  select: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  boolean: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
  ],
  default: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'contains', label: 'Contains' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
};

/**
 * GlobalFilter - Advanced filter modal (SparkCRM pattern)
 *
 * Features:
 * - Multi-condition filter builder (AND/OR logic)
 * - Support for multiple field types: date, number, text, select, boolean
 * - Filter serialization/deserialization for persistence
 * - Null operators support
 */
const GlobalFilter = ({
  visible,
  onCancel,
  onApply,
  filterFields = [],
  initialFilters = null,
  title = 'Filters',
  columns = [],
}) => {
  const { token } = useToken();
  const [conditions, setConditions] = useState([]);
  const [conditionType, setConditionType] = useState('AND');

  // Create field options from filterFields or columns
  const fieldOptions = useMemo(() => {
    const optionsMap = new Map();

    try {
      // Process filterFields first
      if (Array.isArray(filterFields) && filterFields.length > 0) {
        filterFields.forEach((field) => {
          if (field.field && field.label) {
            optionsMap.set(field.field, {
              value: field.field,
              label: field.label || field.headerName || field.field,
              type: field.filterType || 'text',
              options: field.options || [],
            });
          }
        });
      }

      // Then process columns (fallback)
      if (Array.isArray(columns) && columns.length > 0) {
        columns.forEach((col) => {
          if (col.field && col.filterType && !optionsMap.has(col.field)) {
            optionsMap.set(col.field, {
              value: col.field,
              label: col.headerName || col.field,
              type: col.filterType || 'text',
              options: col.filterOptions || [],
            });
          }
        });
      }

      // Convert to array and sort
      return Array.from(optionsMap.values()).sort((a, b) =>
        String(a.label).localeCompare(String(b.label))
      );
    } catch (error) {
      console.error('Error processing field options:', error);
      return [];
    }
  }, [filterFields, columns]);

  // Serialization functions
  const serializeFilterValue = useCallback((value) => {
    if (value === '__NULL_CHECK__') return null;
    if (value === null || value === undefined) return value;

    try {
      if (Array.isArray(value)) {
        if (value.length === 2 && value.every((v) => dayjs.isDayjs(v))) {
          return value.map((v) => v.format(DATE_FORMAT));
        }
        return value;
      }

      if (dayjs.isDayjs(value)) {
        return value.format(DATE_FORMAT);
      }

      if (typeof value === 'object' && value !== null && ('min' in value || 'max' in value)) {
        return {
          min: value.min !== undefined ? Number(value.min) : null,
          max: value.max !== undefined ? Number(value.max) : null,
        };
      }

      return value;
    } catch (error) {
      console.error('Error serializing filter value:', error);
      return value;
    }
  }, []);

  const deserializeFilterValue = useCallback((value, field, operator) => {
    if (value === null || value === undefined) {
      // For null operators, use placeholder
      if (operator === 'is null' || operator === 'is not null') {
        return '__NULL_CHECK__';
      }
      return value;
    }

    try {
      // Check if it's a date field
      const fieldOption = fieldOptions.find((f) => f.value === field);
      const isDateField = fieldOption?.type === 'date';

      if (isDateField) {
        if (typeof value === 'string') {
          return dayjs(value);
        }
        if (Array.isArray(value)) {
          return value.map((dateStr) => (dateStr ? dayjs(dateStr) : null));
        }
      }

      if (typeof value === 'object' && value !== null && ('min' in value || 'max' in value)) {
        return {
          min: value.min !== undefined ? Number(value.min) : null,
          max: value.max !== undefined ? Number(value.max) : null,
        };
      }

      return value;
    } catch (error) {
      console.error('Error deserializing filter value:', error);
      return value;
    }
  }, [fieldOptions]);

  // Initialize conditions on open
  useEffect(() => {
    if (visible) {
      if (initialFilters?.conditions?.length > 0) {
        const loadedConditions = initialFilters.conditions.map((cond, index) => {
          const isNullOperator = cond.operator === 'is null' || cond.operator === 'is not null';
          return {
            id: Date.now() + index,
            field: cond.field,
            operator: cond.operator || 'is',
            value: isNullOperator
              ? '__NULL_CHECK__'
              : deserializeFilterValue(cond.value, cond.field, cond.operator),
          };
        });
        setConditions(loadedConditions);
        setConditionType(initialFilters.type || 'AND');
      } else {
        setConditions([{ ...EMPTY_CONDITION, id: Date.now() }]);
        setConditionType('AND');
      }
    }
  }, [visible, initialFilters, deserializeFilterValue]);

  // Add new condition
  const addCondition = useCallback(() => {
    setConditions((prev) => [...prev, { ...EMPTY_CONDITION, id: Date.now() }]);
  }, []);

  // Remove condition
  const removeCondition = useCallback((index) => {
    setConditions((prev) => {
      const updated = prev.filter((_, i) => i !== index);
      return updated.length === 0 ? [{ ...EMPTY_CONDITION, id: Date.now() }] : updated;
    });
  }, []);

  // Update condition
  const updateCondition = useCallback(
    (index, key, value) => {
      setConditions((prev) =>
        prev.map((cond, i) => {
          if (i !== index) return cond;

          const updated = { ...cond, [key]: value };

          // Reset value when field changes
          if (key === 'field') {
            const fieldOption = fieldOptions.find((opt) => opt.value === value);
            updated.value = null;
            updated.operator = fieldOption?.type === 'date' ? 'between' : 'is';
          }

          // Handle null operators
          if (key === 'operator') {
            if (value === 'is null' || value === 'is not null') {
              updated.value = '__NULL_CHECK__';
            } else if (cond.value === '__NULL_CHECK__') {
              updated.value = null;
            }
          }

          return updated;
        })
      );
    },
    [fieldOptions]
  );

  // Clear all filters
  const handleClear = useCallback(() => {
    setConditions([{ ...EMPTY_CONDITION, id: Date.now() }]);
    setConditionType('AND');
    onApply(null);
    onCancel();
  }, [onApply, onCancel]);

  // Apply filters
  const handleApply = useCallback(() => {
    const validConditions = conditions.filter(
      (c) => c.field && (c.value !== null || c.operator === 'is null' || c.operator === 'is not null')
    );

    if (validConditions.length === 0) {
      onApply(null);
    } else {
      onApply({
        type: conditionType,
        conditions: validConditions.map((c) => ({
          field: c.field,
          operator: c.operator,
          value: serializeFilterValue(c.value),
        })),
      });
    }
    onCancel();
  }, [conditions, conditionType, onApply, onCancel, serializeFilterValue]);

  // Check if apply button should be disabled
  const isApplyDisabled = useMemo(() => {
    return conditions.some((condition) => {
      if (!condition.field) return true;
      if (condition.operator === 'is null' || condition.operator === 'is not null') return false;
      return condition.value === null || condition.value === undefined;
    });
  }, [conditions]);

  // Render value input based on field type
  const renderValueInput = useCallback(
    (condition, fieldOption, index) => {
      if (!fieldOption) return null;

      // Don't render input for null operators
      if (condition.operator === 'is null' || condition.operator === 'is not null') {
        return null;
      }

      const { type, options } = fieldOption;
      const commonProps = { value: condition.value };

      try {
        switch (type) {
          case 'date':
            if (condition.operator === 'is' || condition.operator === 'between') {
              const rangeValue =
                Array.isArray(condition.value) && condition.value.length === 2
                  ? condition.value.map((date) => {
                      if (date && dayjs.isDayjs(date)) return date;
                      if (date && typeof date === 'string') return dayjs(date);
                      return null;
                    })
                  : [null, null];

              return (
                <RangePicker
                  format={DISPLAY_DATE_FORMAT}
                  style={{ width: '100%', minWidth: '100%' }}
                  value={rangeValue}
                  onChange={(dates) => {
                    const normalizedDates = dates
                      ? dates.map((date) => (date ? dayjs(date).startOf('day') : null))
                      : null;
                    updateCondition(index, 'value', normalizedDates);
                  }}
                  placeholder={['Start date', 'End date']}
                />
              );
            }

            const singleDateValue = condition.value
              ? dayjs.isDayjs(condition.value)
                ? condition.value
                : dayjs(condition.value)
              : null;

            return (
              <DatePicker
                format={DISPLAY_DATE_FORMAT}
                style={{ width: '100%', minWidth: '100%' }}
                value={singleDateValue}
                onChange={(date) => {
                  const normalizedDate = date ? dayjs(date).startOf('day') : null;
                  updateCondition(index, 'value', normalizedDate);
                }}
                placeholder={condition.operator === 'after' ? 'After date' : 'Before date'}
              />
            );

          case 'number':
            if (condition.operator === 'between') {
              return (
                <Space style={{ width: '100%' }}>
                  <InputNumber
                    placeholder="Min"
                    style={{ width: '100%' }}
                    value={condition.value?.min}
                    onChange={(val) =>
                      updateCondition(index, 'value', { ...(condition.value || {}), min: val })
                    }
                  />
                  <InputNumber
                    placeholder="Max"
                    style={{ width: '100%' }}
                    value={condition.value?.max}
                    onChange={(val) =>
                      updateCondition(index, 'value', { ...(condition.value || {}), max: val })
                    }
                  />
                </Space>
              );
            }
            return (
              <InputNumber
                style={{ width: '100%' }}
                placeholder="Enter value"
                {...commonProps}
                onChange={(val) => updateCondition(index, 'value', val)}
              />
            );

          case 'select':
            return (
              <Select
                mode="multiple"
                maxTagCount={1}
                style={{ width: '100%', minWidth: '100%' }}
                placeholder="Select option"
                {...commonProps}
                onChange={(val) => updateCondition(index, 'value', val)}
                allowClear
              >
                {options.map((opt) => (
                  <Option key={opt.value} value={opt.value}>
                    {opt.label}
                  </Option>
                ))}
              </Select>
            );

          case 'boolean':
            return (
              <Select
                style={{ width: '100%', minWidth: '100%' }}
                placeholder="Select value"
                value={condition.value}
                onChange={(val) => updateCondition(index, 'value', val)}
              >
                <Option value="yes">Yes</Option>
                <Option value="no">No</Option>
              </Select>
            );

          default: // text
            return (
              <Input
                placeholder="Enter value"
                style={{ width: '100%' }}
                value={condition.value || ''}
                onChange={(e) => updateCondition(index, 'value', e.target.value)}
              />
            );
        }
      } catch (error) {
        console.error('Error rendering value input:', error);
        return (
          <Input
            placeholder="Enter value"
            style={{ width: '100%' }}
            value={condition.value || ''}
            onChange={(e) => updateCondition(index, 'value', e.target.value)}
          />
        );
      }
    },
    [updateCondition]
  );

  if (!visible) return null;

  return (
    <Modal
      title={null}
      open={visible}
      width={900}
      closable={false}
      footer={null}
      maskClosable={true}
      onCancel={onCancel}
    >
      <Flex justify="space-between" align="center" style={{ marginBottom: '16px' }}>
        <Flex align="center">
          <FilterOutlined style={{ marginRight: 8 }} />
          <Text strong style={{ fontSize: '16px' }}>
            {title}
          </Text>
          <Tooltip title="Configure filters to narrow down your results">
            <InfoCircleOutlined style={{ marginLeft: 8, color: token.colorTextSecondary }} />
          </Tooltip>
        </Flex>
        <Space>
          <Button type="text" onClick={handleClear} size="small">
            Clear all
          </Button>
        </Space>
      </Flex>

      <Flex
        vertical
        style={{
          backgroundColor: token.colorBgLayout,
          padding: '16px',
          borderRadius: '8px',
        }}
      >
        {conditions.map((condition, conditionIndex) => {
          const fieldOption = fieldOptions.find((opt) => opt.value === condition.field);
          const operatorOptions = OPERATOR_OPTIONS[fieldOption?.type] || OPERATOR_OPTIONS.default;

          return (
            <Flex
              key={condition.id}
              align="center"
              style={{ marginBottom: '8px', gap: '8px', overflowX: 'auto' }}
            >
              <Flex style={{ width: '80px', minWidth: '50px', justifyContent: 'center' }}>
                {conditionIndex === 0 ? (
                  <Text type="secondary">Where</Text>
                ) : (
                  <Select value={conditionType} onChange={setConditionType} style={{ width: '80px' }} size="middle">
                    <Option value="AND">AND</Option>
                    <Option value="OR">OR</Option>
                  </Select>
                )}
              </Flex>

              <Select
                placeholder="Select filter"
                value={condition.field}
                onChange={(value) => updateCondition(conditionIndex, 'field', value)}
                style={{ width: '200px', minWidth: '180px' }}
                showSearch
                optionFilterProp="children"
                filterOption={(input, option) =>
                  (option?.children?.toString()?.toLowerCase() ?? '').includes(input.toLowerCase())
                }
                size="middle"
              >
                {fieldOptions.map((option) => (
                  <Option key={option.value} value={option.value}>
                    {option.label}
                  </Option>
                ))}
              </Select>

              <Select
                value={condition.operator}
                onChange={(value) => updateCondition(conditionIndex, 'operator', value)}
                style={{ width: '150px', minWidth: '120px' }}
                size="middle"
              >
                {operatorOptions.map((op) => (
                  <Option key={op.value} value={op.value}>
                    {op.label}
                  </Option>
                ))}
              </Select>

              <div style={{ flex: 1, minWidth: '200px' }}>
                {renderValueInput(condition, fieldOption, conditionIndex)}
              </div>

              <Button
                type="text"
                danger
                icon={<DeleteOutlined />}
                onClick={() => removeCondition(conditionIndex)}
              />
            </Flex>
          );
        })}

        <Flex style={{ marginTop: '8px' }}>
          <Button type="text" icon={<PlusOutlined />} size="small" onClick={addCondition} style={{ fontSize: '12px' }}>
            Add filter
          </Button>
        </Flex>
      </Flex>

      <Flex justify="flex-end" gap="small" style={{ marginTop: '16px' }}>
        <Button onClick={onCancel}>Cancel</Button>
        <Button type="primary" onClick={handleApply} disabled={isApplyDisabled}>
          Apply
        </Button>
      </Flex>
    </Modal>
  );
};

export default GlobalFilter;
