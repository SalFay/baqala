import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Input, Tag } from 'antd'
import { DollarOutlined, ShoppingOutlined, RiseOutlined } from '@ant-design/icons'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import LocationSelector from '@/Components/LocationSelector'
import { formatCurrency } from '@/Helpers/formatters'

const { Search } = Input

export default function StockValuation() {
  const { data, filters } = usePage().props
  const [searchText, setSearchText] = useState('')
  const [locationId, setLocationId] = useState(filters?.locationId)

  const items = data?.items || []
  const summary = data?.summary || {}

  const handleLocationChange = (value) => {
    setLocationId(value)
    router.get(route('reports.stock-valuation'), {
      location_id: value,
    }, { preserveState: true })
  }

  const filteredItems = items.filter(item =>
    !searchText ||
    item.name?.toLowerCase().includes(searchText.toLowerCase()) ||
    item.sku?.toLowerCase().includes(searchText.toLowerCase())
  )

  const columns = [
    {
      title: 'Product',
      key: 'product',
      render: (_, record) => (
        <div>
          <div style={{ fontWeight: 500 }}>{record.name}</div>
          <div style={{ fontSize: 12, color: '#999' }}>{record.sku}</div>
        </div>
      ),
    },
    {
      title: 'Category',
      dataIndex: 'category',
      key: 'category',
      filters: [...new Set(items.map(i => i.category))].map(c => ({ text: c, value: c })),
      onFilter: (value, record) => record.category === value,
    },
    {
      title: 'Qty',
      dataIndex: 'quantity',
      key: 'quantity',
      align: 'right',
      sorter: (a, b) => a.quantity - b.quantity,
      render: (value) => (
        <Tag color={value > 0 ? 'green' : 'red'}>{value}</Tag>
      ),
    },
    {
      title: 'Cost Price',
      dataIndex: 'cost_price',
      key: 'cost_price',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Sell Price',
      dataIndex: 'selling_price',
      key: 'selling_price',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Cost Value',
      dataIndex: 'cost_value',
      key: 'cost_value',
      align: 'right',
      render: (value) => formatCurrency(value),
      sorter: (a, b) => a.cost_value - b.cost_value,
    },
    {
      title: 'Retail Value',
      dataIndex: 'retail_value',
      key: 'retail_value',
      align: 'right',
      render: (value) => formatCurrency(value),
      sorter: (a, b) => a.retail_value - b.retail_value,
    },
    {
      title: 'Potential Profit',
      dataIndex: 'potential_profit',
      key: 'potential_profit',
      align: 'right',
      render: (value) => (
        <span style={{ color: value >= 0 ? '#52c41a' : '#ff4d4f' }}>
          {formatCurrency(value)}
        </span>
      ),
      sorter: (a, b) => a.potential_profit - b.potential_profit,
    },
  ]

  return (
    <>
      <Head title="Stock Valuation Report" />

      <GlobalPageHeader
        title="Stock Valuation Report"
        parentPageTitle="Reports"
      />

      {/* Filters */}
      <Card size="small" style={{ marginBottom: 16 }}>
        <Row gutter={16} align="middle">
          <Col>
            <LocationSelector
              value={locationId}
              onChange={handleLocationChange}
              placeholder="All Locations"
              allowClear
              style={{ width: 200 }}
            />
          </Col>
          <Col flex="auto">
            <Search
              placeholder="Search products..."
              allowClear
              onSearch={setSearchText}
              onChange={(e) => !e.target.value && setSearchText('')}
              style={{ maxWidth: 300 }}
            />
          </Col>
        </Row>
      </Card>

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Total Items"
              value={summary.total_items || 0}
              prefix={<ShoppingOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Total Quantity"
              value={summary.total_quantity || 0}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Cost Value"
              value={summary.total_cost_value || 0}
              prefix={<DollarOutlined />}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Retail Value"
              value={summary.total_retail_value || 0}
              precision={2}
              valueStyle={{ color: '#1890ff' }}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8} lg={4}>
          <Card>
            <Statistic
              title="Potential Profit"
              value={summary.potential_profit || 0}
              prefix={<RiseOutlined />}
              precision={2}
              valueStyle={{ color: '#52c41a' }}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Inventory Valuation">
        <Table
          dataSource={filteredItems}
          columns={columns}
          rowKey="id"
          pagination={{ pageSize: 50, showSizeChanger: true }}
          size="small"
          scroll={{ x: 1000 }}
        />
      </Card>
    </>
  )
}
