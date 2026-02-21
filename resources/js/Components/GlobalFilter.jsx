import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import {
  Button,
  DatePicker,
  Flex,
  Form,
  Input,
  InputNumber,
  Modal,
  Select,
  Space,
  theme,
  Tooltip,
  Typography,
} from 'antd'
import { DeleteOutlined, InfoCircleOutlined, PlusOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import Button1 from '@/Components/Buttons/Button1.jsx'

const { Text } = Typography
const { Option } = Select
const { RangePicker } = DatePicker
const { useToken } = theme

// Constants
const EMPTY_CONDITION = { field: null, operator: 'is', value: null }
const DATE_FORMAT = 'YYYY-MM-DD'
const DISPLAY_DATE_FORMAT = 'MM-DD-YYYY'

// Operator options by field type
const OPERATOR_OPTIONS = {
  date: [
    { value: 'is', label: 'Is' },
    { value: 'between', label: 'Between' },
    { value: 'after', label: 'After' },
    { value: 'before', label: 'Before' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  range: [
    { value: 'is', label: 'Equal' },
    { value: 'between', label: 'Between' },
    { value: 'greater than', label: 'Greater than' },
    { value: 'less than', label: 'Less than' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  text: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'contains', label: 'Contains' },
    { value: 'does not contain', label: 'Does not contain' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  select: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
  default: [
    { value: 'is', label: 'Is' },
    { value: 'is not', label: 'Is not' },
    { value: 'contains', label: 'Contains' },
    { value: 'is null', label: 'Is Null' },
    { value: 'is not null', label: 'Is Not Null' },
  ],
}

const GlobalFilter = ({
  visible,
  handleCancel,
  onApplyFilters,
  filterFields = {},
  moduleName,
  storageKey,
  initialFilters = null,
  columns = [],
}) => {
  const { token } = useToken()
  const [form] = Form.useForm()
  const firstSelectRef = useRef(null)
  const [conditions, setConditions] = useState([])
  const [conditionType, setConditionType] = useState('AND')
  const [openDropdowns, setOpenDropdowns] = useState({})

  // Memoized field options
  const fieldOptions = useMemo(() => {
    const optionsMap = new Map()

    try {
      // Process filterFields
      const processFilterFields = (fields, type) => {
        if (!fields || typeof fields !== 'object') return

        Object.entries(fields).forEach(([key, field]) => {
          if (!field || typeof field !== 'object') return
          if (!field.name || !field.label) return

          optionsMap.set(field.name, {
            value: field.name,
            label: field.label,
            type,
            selectType: field.type || 'static',
            params: field.params || {},
          })
        })
      }

      if (filterFields && typeof filterFields === 'object') {
        if (filterFields.DATES) processFilterFields(filterFields.DATES, 'date')
        if (filterFields.SELECTS) processFilterFields(filterFields.SELECTS, 'select')
        if (filterFields.RANGES) processFilterFields(filterFields.RANGES, 'range')
        if (filterFields.TEXTS) processFilterFields(filterFields.TEXTS, 'text')
      }

      // Process columns
      if (Array.isArray(columns)) {
        columns.forEach((col) => {
          if (!col?.context?.filterType || !col?.field) return
          if (optionsMap.has(col.field)) return

          optionsMap.set(col.field, {
            value: col.field,
            label: col.headerName || col.field,
            type: col.context?.filterType,
            selectType: col.context?.selectType,
            params: col.context?.filterParams || {},
          })
        })
      }

      return Array.from(optionsMap.values()).sort((a, b) =>
        String(a.label).localeCompare(String(b.label))
      )
    } catch (error) {
      console.error('Error processing field options:', error)
      return []
    }
  }, [filterFields, columns])

  // Serialization functions
  const serializeFilterValue = useCallback((value) => {
    if (value === '__NULL_CHECK__') return null
    if (value === null || value === undefined) return value

    if (Array.isArray(value)) {
      if (value.length === 2 && value.every((v) => dayjs.isDayjs(v))) {
        return value.map((v) => v.format(DATE_FORMAT))
      }
      return value
    }

    if (dayjs.isDayjs(value)) {
      return value.format(DATE_FORMAT)
    }

    if (typeof value === 'object' && value !== null && ('min' in value || 'max' in value)) {
      return {
        min: value.min !== undefined ? Number(value.min) : null,
        max: value.max !== undefined ? Number(value.max) : null,
      }
    }

    return value
  }, [])

  const deserializeFilterValue = useCallback((value, field) => {
    if (value === null || value === undefined || !field) return value

    const isDateField = field && (
      field.includes('date') || field.includes('Date') ||
      field.includes('_at') || field.includes('created')
    )

    if (isDateField) {
      if (typeof value === 'string') return dayjs(value)
      if (Array.isArray(value)) {
        return value.map((dateStr) => dateStr ? dayjs(dateStr) : null)
      }
    }

    if (typeof value === 'object' && value !== null && ('min' in value || 'max' in value)) {
      return {
        min: value.min !== undefined ? Number(value.min) : null,
        max: value.max !== undefined ? Number(value.max) : null,
      }
    }

    return value
  }, [])

  // Initialize conditions
  const initializeEmptyCondition = useCallback(() => {
    setConditions([{ ...EMPTY_CONDITION, id: Date.now() }])
    setConditionType('AND')
    setOpenDropdowns({ '0': true })
  }, [])

  // Load filters effect
  useEffect(() => {
    if (!visible) {
      setConditions([])
      setOpenDropdowns({})
      return
    }

    try {
      let loadedConditions = []
      let loadedType = 'AND'

      if (initialFilters?.conditions?.length > 0) {
        loadedConditions = initialFilters.conditions.map((cond, index) => {
          const isNullOperator = cond.operator === 'is null' || cond.operator === 'is not null'
          const deserializedValue = deserializeFilterValue(cond.value, cond.field)

          return {
            id: Date.now() + index,
            field: cond.field,
            operator: cond.operator || 'is',
            value: isNullOperator ? '__NULL_CHECK__' : deserializedValue,
          }
        })
        loadedType = initialFilters.type || 'AND'
      } else if (storageKey) {
        try {
          const savedFilters = localStorage.getItem(storageKey)
          if (savedFilters) {
            const parsedFilters = JSON.parse(savedFilters)
            if (parsedFilters.conditions?.length > 0) {
              loadedConditions = parsedFilters.conditions.map((cond, index) => {
                const isNullOperator = cond.operator === 'is null' || cond.operator === 'is not null'
                const deserializedValue = deserializeFilterValue(cond.value, cond.field)

                return {
                  id: Date.now() + index,
                  field: cond.field,
                  operator: cond.operator || 'is',
                  value: isNullOperator ? '__NULL_CHECK__' : deserializedValue,
                }
              })
              loadedType = parsedFilters.type || 'AND'
            }
          }
        } catch (storageError) {
          console.error('Error parsing saved filters:', storageError)
        }
      }

      if (loadedConditions.length > 0) {
        setConditions(loadedConditions)
        setConditionType(loadedType)
        setOpenDropdowns({ '0': true })
      } else {
        initializeEmptyCondition()
      }
    } catch (error) {
      console.error('Error loading filters:', error)
      initializeEmptyCondition()
    }
  }, [visible, storageKey, initialFilters, deserializeFilterValue, initializeEmptyCondition])

  // Condition management
  const addCondition = useCallback(() => {
    const newCondition = { ...EMPTY_CONDITION, id: Date.now() }
    setConditions(prev => [...prev, newCondition])
    setOpenDropdowns(prev => ({ ...prev, [`${conditions.length}`]: true }))
  }, [conditions.length])

  const updateCondition = useCallback((conditionIndex, key, value) => {
    setConditions(prev => prev.map((condition, idx) => {
      if (idx !== conditionIndex) return condition

      let updatedCondition = { ...condition, [key]: value }

      if (key === 'field') {
        const fieldOption = fieldOptions.find(opt => opt.value === value)
        updatedCondition.operator = fieldOption?.type === 'date' ? 'between' : 'is'
        updatedCondition.value = null
      }

      if (key === 'operator') {
        if (value === 'is null' || value === 'is not null') {
          updatedCondition.value = '__NULL_CHECK__'
        } else if (condition.value === '__NULL_CHECK__') {
          updatedCondition.value = null
        }
      }

      return updatedCondition
    }))

    if (key === 'field') {
      setOpenDropdowns(prev => ({ ...prev, [`${conditionIndex}`]: false }))
    }
  }, [fieldOptions])

  const removeCondition = useCallback((conditionIndex) => {
    setConditions(prev => {
      const updated = prev.filter((_, idx) => idx !== conditionIndex)
      return updated.length === 0 ? [{ ...EMPTY_CONDITION, id: Date.now() }] : updated
    })
  }, [])

  const handleResetFields = useCallback(() => {
    form.resetFields()
    setConditions([])
    setConditionType('AND')
    setOpenDropdowns({})

    if (storageKey) {
      localStorage.removeItem(storageKey)
    }

    onApplyFilters({})
    initializeEmptyCondition()
    handleCancel()
  }, [form, onApplyFilters, handleCancel, storageKey, initializeEmptyCondition])

  const submitFilters = useCallback(() => {
    const validConditions = conditions.filter(condition =>
      condition.field && condition.value !== null && condition.value !== undefined
    )

    const filterData = validConditions.length > 0 ? {
      type: conditionType,
      conditions: validConditions.map(condition => ({
        field: condition.field,
        operator: condition.operator,
        value: serializeFilterValue(condition.value),
      }))
    } : {}

    if (storageKey) {
      if (Object.keys(filterData).length > 0) {
        localStorage.setItem(storageKey, JSON.stringify(filterData))
      } else {
        localStorage.removeItem(storageKey)
      }
    }

    onApplyFilters(filterData)
    handleCancel()
  }, [conditions, conditionType, serializeFilterValue, onApplyFilters, handleCancel, storageKey])

  // Value input renderer
  const renderValueInput = useCallback((conditionIndex, condition, fieldOption) => {
    if (!fieldOption) return null

    if (condition.operator === 'is null' || condition.operator === 'is not null') {
      return null
    }

    const commonProps = { value: condition.value }

    switch (fieldOption.type) {
      case 'date':
        if (condition.operator === 'after' || condition.operator === 'before') {
          const singleDateValue = condition.value
            ? (dayjs.isDayjs(condition.value) ? condition.value : dayjs(condition.value))
            : null

          return (
            <DatePicker
              format={DISPLAY_DATE_FORMAT}
              style={{ width: '100%' }}
              value={singleDateValue}
              onChange={(date) => {
                const normalizedDate = date ? dayjs(date).startOf('day') : null
                updateCondition(conditionIndex, 'value', normalizedDate)
              }}
              placeholder={condition.operator === 'after' ? 'After date' : 'Before date'}
            />
          )
        }

        const rangeValue = Array.isArray(condition.value) && condition.value.length === 2
          ? condition.value.map(date => {
            if (date && dayjs.isDayjs(date)) return date
            if (date && typeof date === 'string') return dayjs(date)
            return null
          })
          : [null, null]

        return (
          <RangePicker
            format={DISPLAY_DATE_FORMAT}
            style={{ width: '100%' }}
            value={rangeValue}
            onChange={(dates) => {
              const normalizedDates = dates
                ? dates.map(date => date ? dayjs(date).startOf('day') : null)
                : null
              updateCondition(conditionIndex, 'value', normalizedDates)
            }}
            placeholder={['Start date', 'End date']}
          />
        )

      case 'select':
        return (
          <Select
            mode="multiple"
            maxTagCount={1}
            allowClear
            placeholder="Select option"
            style={{ width: '100%' }}
            {...commonProps}
            onChange={(value) => updateCondition(conditionIndex, 'value', value)}
          >
            {(fieldOption.params?.options || []).map(opt => (
              <Option key={opt.value} value={opt.value}>{opt.label}</Option>
            ))}
          </Select>
        )

      case 'range':
        if (condition.operator !== 'between') {
          return (
            <InputNumber
              prefix={fieldOption.params?.prefix}
              placeholder="Value"
              style={{ width: '100%' }}
              value={condition.value}
              onChange={(value) => updateCondition(conditionIndex, 'value', value)}
            />
          )
        }

        return (
          <Space style={{ width: '100%' }}>
            <InputNumber
              prefix={fieldOption.params?.prefix}
              placeholder="Min"
              style={{ width: '100%' }}
              onChange={(value) =>
                updateCondition(conditionIndex, 'value', {
                  ...(condition.value || {}),
                  min: value,
                })
              }
              value={condition.value?.min}
            />
            <InputNumber
              prefix={fieldOption.params?.prefix}
              placeholder="Max"
              style={{ width: '100%' }}
              onChange={(value) =>
                updateCondition(conditionIndex, 'value', {
                  ...(condition.value || {}),
                  max: value,
                })
              }
              value={condition.value?.max}
            />
          </Space>
        )

      default:
        return (
          <Input
            placeholder="Enter value"
            style={{ width: '100%' }}
            value={condition.value || ''}
            onChange={(e) => updateCondition(conditionIndex, 'value', e.target.value)}
          />
        )
    }
  }, [updateCondition])

  // Check if apply button should be disabled
  const isApplyDisabled = useMemo(() => {
    return conditions.some(condition => {
      if (!condition.field) return true
      if (condition.operator === 'is null' || condition.operator === 'is not null') {
        return false
      }
      return condition.value === null || condition.value === undefined
    })
  }, [conditions])

  if (!visible) return null

  return (
    <Modal
      title={null}
      open={visible}
      width={900}
      closable={false}
      footer={null}
      maskClosable={true}
      onCancel={handleCancel}
    >
      <Flex justify="space-between" align="center" style={{ marginBottom: '16px' }}>
        <Flex align="center">
          <Text strong style={{ fontSize: '16px' }}>
            Filters for {moduleName}
          </Text>
          <Tooltip title={`Configure filters for ${moduleName}`}>
            <InfoCircleOutlined style={{ marginLeft: 8, color: token.colorTextSecondary }} />
          </Tooltip>
        </Flex>
        <Button1 type="text" onClick={handleResetFields} size="small">
          Clear all
        </Button1>
      </Flex>

      <Form form={form} layout="vertical">
        <Flex
          vertical
          style={{
            backgroundColor: token.colorBgLayout,
            padding: '16px',
            borderRadius: '8px',
          }}
        >
          {conditions.map((condition, conditionIndex) => {
            const fieldOption = fieldOptions.find(opt => opt.value === condition.field)
            const operatorOptions = OPERATOR_OPTIONS[fieldOption?.type] || OPERATOR_OPTIONS.default

            return (
              <Flex key={condition.id} align="center" style={{ marginBottom: '8px', gap: '8px' }}>
                <Flex style={{ width: '80px', justifyContent: 'center' }}>
                  {conditionIndex === 0 ? (
                    <Text type="secondary">Where</Text>
                  ) : (
                    <Select
                      value={conditionType}
                      onChange={setConditionType}
                      style={{ width: '80px' }}
                      size="middle"
                    >
                      <Option value="AND">AND</Option>
                      <Option value="OR">OR</Option>
                    </Select>
                  )}
                </Flex>

                <Select
                  ref={conditionIndex === 0 ? firstSelectRef : null}
                  placeholder="Select filter"
                  value={condition.field}
                  onChange={(value) => updateCondition(conditionIndex, 'field', value)}
                  autoFocus={conditionIndex === 0}
                  onOpenChange={(open) =>
                    setOpenDropdowns(prev => ({ ...prev, [`${conditionIndex}`]: open }))
                  }
                  open={openDropdowns[`${conditionIndex}`] ?? false}
                  style={{ width: '200px' }}
                  showSearch
                  optionFilterProp="children"
                  size="middle"
                >
                  {fieldOptions.map(option => (
                    <Option key={option.value} value={option.value}>
                      {option.label}
                    </Option>
                  ))}
                </Select>

                <Select
                  value={condition.operator}
                  onChange={(value) => updateCondition(conditionIndex, 'operator', value)}
                  style={{ width: '130px' }}
                  size="middle"
                >
                  {operatorOptions.map(op => (
                    <Option key={op.value} value={op.value}>
                      {op.label}
                    </Option>
                  ))}
                </Select>

                <div style={{ flex: 1 }}>
                  {renderValueInput(conditionIndex, condition, fieldOption)}
                </div>

                <Button1
                  icon={<DeleteOutlined />}
                  onClick={() => removeCondition(conditionIndex)}
                  style={{ color: token.colorError }}
                />
              </Flex>
            )
          })}

          <Flex style={{ marginTop: '8px' }}>
            <Button
              type="text"
              icon={<PlusOutlined />}
              size="small"
              onClick={addCondition}
              style={{ fontSize: '12px' }}
            >
              Add filter
            </Button>
          </Flex>
        </Flex>

        <Flex justify="flex-end" gap="small" style={{ marginTop: '16px' }}>
          <Button onClick={handleCancel}>Cancel</Button>
          <Button1 size={'default'} onClick={submitFilters} disabled={isApplyDisabled}>
            Apply
          </Button1>
        </Flex>
      </Form>
    </Modal>
  )
}

export default React.memo(GlobalFilter)
