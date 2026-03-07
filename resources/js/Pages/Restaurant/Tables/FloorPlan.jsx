import { useState, useEffect } from 'react'
import { Card, Select, Space, Tooltip, Badge, Spin, Typography, Row, Col } from 'antd'
import { useQuery } from '@tanstack/react-query'
import { fetchFloorPlan } from '@/Helpers/api/restaurantService'

const { Text } = Typography

const statusColors = {
  available: '#52c41a',
  occupied: '#ff4d4f',
  reserved: '#faad14',
  maintenance: '#d9d9d9',
}

export default function FloorPlan({ onEdit }) {
  const [selectedFloor, setSelectedFloor] = useState(null)

  const { data, isLoading, refetch } = useQuery({
    queryKey: ['floorPlan', selectedFloor],
    queryFn: () => fetchFloorPlan({ floor: selectedFloor }).then(res => res.data),
    refetchInterval: 30000, // Auto-refresh every 30 seconds
  })

  const tables = data?.tables || []
  const floors = data?.floors || []
  const sections = data?.sections || []

  // Group tables by section
  const tablesBySection = tables.reduce((acc, table) => {
    const section = table.section || 'Unassigned'
    if (!acc[section]) acc[section] = []
    acc[section].push(table)
    return acc
  }, {})

  const getTableStyle = (table) => {
    const baseStyle = {
      width: table.shape === 'rectangle' ? 100 : 70,
      height: 70,
      borderRadius: table.shape === 'circle' ? '50%' : 8,
      backgroundColor: statusColors[table.status],
      display: 'flex',
      flexDirection: 'column',
      alignItems: 'center',
      justifyContent: 'center',
      cursor: 'pointer',
      transition: 'transform 0.2s, box-shadow 0.2s',
      color: '#fff',
      fontWeight: 'bold',
    }
    return baseStyle
  }

  if (isLoading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', padding: 50 }}>
        <Spin size="large" />
      </div>
    )
  }

  return (
    <div>
      {/* Filters */}
      <Card style={{ marginBottom: 16 }}>
        <Space>
          <Text>Floor:</Text>
          <Select
            value={selectedFloor}
            onChange={setSelectedFloor}
            style={{ width: 150 }}
            placeholder="All Floors"
            allowClear
          >
            {floors.map(floor => (
              <Select.Option key={floor} value={floor}>{floor}</Select.Option>
            ))}
          </Select>

          {/* Legend */}
          <div style={{ marginLeft: 24, display: 'flex', gap: 16 }}>
            {Object.entries(statusColors).map(([status, color]) => (
              <Space key={status}>
                <div style={{
                  width: 16,
                  height: 16,
                  borderRadius: 4,
                  backgroundColor: color,
                }} />
                <Text style={{ textTransform: 'capitalize' }}>{status}</Text>
              </Space>
            ))}
          </div>
        </Space>
      </Card>

      {/* Floor Plan */}
      {Object.entries(tablesBySection).map(([section, sectionTables]) => (
        <Card
          key={section}
          title={section}
          style={{ marginBottom: 16 }}
          bodyStyle={{ minHeight: 150 }}
        >
          <Row gutter={[16, 16]}>
            {sectionTables.map(table => (
              <Col key={table.id}>
                <Tooltip
                  title={
                    <div>
                      <div><strong>{table.name}</strong></div>
                      <div>Capacity: {table.capacity}</div>
                      <div>Status: {table.status}</div>
                      {table.has_order && <div>Has active order</div>}
                    </div>
                  }
                >
                  <div
                    style={getTableStyle(table)}
                    onClick={() => onEdit?.(table)}
                    onMouseEnter={(e) => {
                      e.currentTarget.style.transform = 'scale(1.05)'
                      e.currentTarget.style.boxShadow = '0 4px 12px rgba(0,0,0,0.15)'
                    }}
                    onMouseLeave={(e) => {
                      e.currentTarget.style.transform = 'scale(1)'
                      e.currentTarget.style.boxShadow = 'none'
                    }}
                  >
                    <div style={{ fontSize: 14 }}>{table.name}</div>
                    <div style={{ fontSize: 11, opacity: 0.9 }}>{table.capacity} seats</div>
                  </div>
                </Tooltip>
              </Col>
            ))}
          </Row>
        </Card>
      ))}

      {tables.length === 0 && (
        <Card>
          <div style={{ textAlign: 'center', padding: 40, color: '#999' }}>
            No tables found. Add some tables to see them here.
          </div>
        </Card>
      )}
    </div>
  )
}
