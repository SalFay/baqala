import { useState, useEffect } from 'react'
import { Select, Space, Tag, Spin } from 'antd'
import { useQuery } from '@tanstack/react-query'
import { fetchAllTables } from '@/Helpers/api/restaurantService'

const statusColors = {
  available: 'green',
  occupied: 'red',
  reserved: 'orange',
  maintenance: 'default',
}

export default function TableSelector({
  value,
  onChange,
  placeholder = 'Select table',
  disabled = false,
  allowClear = true,
  showStatus = true,
  statusFilter = null, // 'available', 'occupied', etc.
  locationId = null,
  style = {},
  ...props
}) {
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['allTables', locationId, statusFilter],
    queryFn: () => fetchAllTables({
      location_id: locationId,
      status: statusFilter,
    }).then(res => res.data),
    staleTime: 30000,
  })

  const tables = data?.tables || data || []

  // Filter tables by search
  const filteredTables = tables.filter(table => {
    if (!search) return true
    return table.name.toLowerCase().includes(search.toLowerCase()) ||
           table.section?.toLowerCase().includes(search.toLowerCase())
  })

  const handleChange = (selectedValue) => {
    onChange?.(selectedValue)
  }

  return (
    <Select
      value={value}
      onChange={handleChange}
      placeholder={placeholder}
      disabled={disabled}
      allowClear={allowClear}
      showSearch
      filterOption={false}
      onSearch={setSearch}
      loading={isLoading}
      style={{ width: '100%', ...style }}
      notFoundContent={isLoading ? <Spin size="small" /> : 'No tables found'}
      optionLabelProp="label"
      {...props}
    >
      {filteredTables.map(table => (
        <Select.Option
          key={table.id}
          value={table.id}
          label={table.name}
          disabled={table.status === 'occupied' && statusFilter !== 'occupied'}
        >
          <Space style={{ width: '100%', justifyContent: 'space-between' }}>
            <span>
              {table.name}
              {table.section && (
                <span style={{ color: '#999', marginLeft: 8, fontSize: 12 }}>
                  ({table.section})
                </span>
              )}
            </span>
            <Space>
              <span style={{ fontSize: 12, color: '#666' }}>
                {table.capacity} seats
              </span>
              {showStatus && (
                <Tag color={statusColors[table.status]} style={{ margin: 0 }}>
                  {table.status}
                </Tag>
              )}
            </Space>
          </Space>
        </Select.Option>
      ))}
    </Select>
  )
}
