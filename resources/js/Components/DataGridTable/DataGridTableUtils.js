import React from 'react'

// Maximum character length for mobile display text
export const MAX_DISPLAY_LENGTH = 60

// Extract displayable string from any object (recursive)
export const extractDisplayValue = (obj) => {
  if (obj === null || obj === undefined) return null
  if (typeof obj !== 'object') return obj
  if (React.isValidElement(obj)) return obj

  // Priority properties to extract
  const displayProps = ['name', 'full_name', 'title', 'label', 'email', 'value', 'text']
  for (const prop of displayProps) {
    if (obj[prop] !== undefined && obj[prop] !== null && obj[prop] !== '') {
      // Recursively extract if the property is also an object
      const extracted = extractDisplayValue(obj[prop])
      if (extracted !== null && typeof extracted !== 'object') return extracted
    }
  }
  return null
}

// Safely convert any value to a displayable string
export const toDisplayString = (value) => {
  if (value === null || value === undefined) return '-'
  if (React.isValidElement(value)) return value

  // Handle primitives
  if (typeof value !== 'object') {
    const str = String(value).trim()
    return str === '' ? '-' : str
  }

  // Handle arrays
  if (Array.isArray(value)) {
    if (value.length === 0) return '-'
    const items = value
      .map(item => {
        if (typeof item !== 'object' || item === null) return String(item)
        const extracted = extractDisplayValue(item)
        return extracted !== null ? String(extracted) : null
      })
      .filter(item => item !== null && item !== '')
    return items.length > 0 ? items.join(', ') : '-'
  }

  // Handle objects
  const extracted = extractDisplayValue(value)
  return extracted !== null ? String(extracted) : '-'
}

// Truncate text with ellipsis
export const truncateText = (text, maxLength = MAX_DISPLAY_LENGTH) => {
  if (typeof text !== 'string') return text
  if (text.length <= maxLength) return text
  return text.substring(0, maxLength).trim() + '...'
}

// Check if a value is valid for display (not empty, null, or placeholder)
export const isValidDisplayValue = (value) => {
  if (value === null || value === undefined || value === '-' || value === '') return false
  if (React.isValidElement(value)) return true
  if (typeof value !== 'object') return String(value).trim() !== ''

  // For arrays, check if any items exist
  if (Array.isArray(value)) return value.length > 0

  // For objects, check if we can extract a display value
  return extractDisplayValue(value) !== null
}

// Get raw value from row by field path (supports nested fields like 'organization.id')
export const getValue = (row, field) => {
  if (!row || !field) return '-'

  // Direct field access
  const directValue = row[field]
  if (directValue !== undefined && directValue !== null && directValue !== '') {
    return directValue
  }

  // Nested field access (e.g., 'organization.name')
  if (field.includes('.')) {
    const parts = field.split('.')
    let value = row
    for (const part of parts) {
      value = value?.[part]
      if (value === undefined || value === null) break
    }
    if (value !== undefined && value !== null && value !== '') return value
  }

  // Boolean handling
  if (typeof directValue === 'boolean') {
    return directValue ? 'Yes' : 'No'
  }

  return '-'
}

// Get display value for a column - uses valueGetter if available, otherwise getValue
export const getColumnDisplayValue = (row, column) => {
  if (!row || !column) return '-'

  if (column.valueGetter) {
    try {
      return column.valueGetter({ data: row, value: getValue(row, column.field) })
    } catch {
      return '-'
    }
  }
  return getValue(row, column.field)
}

// Find the primary display value for mobile card headers
export const getPrimaryDisplayValue = (row, columns) => {
  if (!row) return '-'

  // Priority fields for common naming patterns
  const priorityFields = ['full_name', 'name', 'title', 'email', 'phone', 'sku', 'barcode']

  // Check priority fields first (direct row access)
  for (const field of priorityFields) {
    const value = row[field]
    if (value && typeof value === 'string' && value.trim() !== '') {
      return value
    }
  }

  // Check nested name fields for related entities
  const nestedNameFields = ['customer.name', 'category.name', 'vendor.name', 'user.name']
  for (const field of nestedNameFields) {
    const value = getValue(row, field)
    if (isValidDisplayValue(value) && typeof value === 'string') {
      return value
    }
  }

  // Fallback: check columns with valueGetter first (they usually have formatted values)
  for (const column of columns) {
    if (column.field === 'actions' || column.field === 'id') continue
    if (!column.valueGetter) continue

    const value = getColumnDisplayValue(row, column)
    const displayStr = toDisplayString(value)
    if (displayStr && displayStr !== '-') {
      return displayStr
    }
  }

  // Final fallback: first non-action column with valid value
  for (const column of columns) {
    if (column.field === 'actions' || column.field === 'id') continue

    const value = getColumnDisplayValue(row, column)
    const displayStr = toDisplayString(value)
    if (displayStr && displayStr !== '-') {
      return displayStr
    }
  }

  // Ultimate fallback: show ID
  return row.id ? `#${row.id}` : '-'
}
