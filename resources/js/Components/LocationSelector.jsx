import { useState, useEffect } from 'react'
import { Select } from 'antd'
import { fetchAllLocations } from '@/Helpers/api/locationService'

/**
 * Reusable location selector component
 *
 * @param {Object} props
 * @param {string|number} props.value - Selected location ID
 * @param {Function} props.onChange - Change handler
 * @param {boolean} props.showAll - Show "All Locations" option
 * @param {boolean} props.disabled - Disable the selector
 * @param {string} props.placeholder - Placeholder text
 * @param {Object} props.style - Additional styles
 */
export default function LocationSelector({
  value,
  onChange,
  showAll = false,
  disabled = false,
  placeholder = 'Select location',
  style = {},
  allowClear = false,
  ...props
}) {
  const [locations, setLocations] = useState([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    loadLocations()
  }, [])

  const loadLocations = async () => {
    setLoading(true)
    try {
      const response = await fetchAllLocations()
      setLocations(response.data.data || [])
    } catch (error) {
      console.error('Failed to load locations:', error)
    } finally {
      setLoading(false)
    }
  }

  const options = [
    ...(showAll ? [{ label: 'All Locations', value: '' }] : []),
    ...locations.map(location => ({
      label: location.name,
      value: location.id,
    })),
  ]

  return (
    <Select
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      disabled={disabled}
      loading={loading}
      options={options}
      allowClear={allowClear}
      style={{ minWidth: 150, ...style }}
      {...props}
    />
  )
}
