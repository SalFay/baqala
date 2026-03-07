import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Tag, Select, Space } from 'antd'
import { WarningOutlined, ClockCircleOutlined, ExclamationCircleOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatDate } from '@/Helpers/formatters'

export default function ExpiryReport() {
  const { items, filters } = usePage().props
  const [daysAhead, setDaysAhead] = useState(filters?.daysAhead || 30)

  const handleDaysChange = (value) => {
    setDaysAhead(value)
    router.get(route('reports.expiry'), {
      days_ahead: value,
    }, { preserveState: true })
  }

  const columns = [
    {
      title: 'Product',
      key: 'product',
      render: (_, record) => (
        <div>
          <div style={{ fontWeight: 500 }}>{record.product_name}</div>
          <div style={{ fontSize: 12, color: '#999' }}>{record.sku}</div>
        </div>
      ),
    },
    {
      title: 'Batch #',
      dataIndex: 'batch_number',
      key: 'batch_number',
    },
    {
      title: 'Quantity',
      dataIndex: 'quantity',
      key: 'quantity',
      align: 'right',
    },
    {
      title: 'Expiry Date',
      dataIndex: 'expiry_date',
      key: 'expiry_date',
      render: (value) => formatDate(value),
      sorter: (a, b) => dayjs(a.expiry_date).unix() - dayjs(b.expiry_date).unix(),
    },
    {
      title: 'Days Left',
      dataIndex: 'days_until_expiry',
      key: 'days_until_expiry',
      align: 'center',
      render: (value, record) => {
        if (record.is_expired) {
          return <Tag color="red" icon={<ExclamationCircleOutlined />}>EXPIRED</Tag>
        }
        if (value <= 7) {
          return <Tag color="red" icon={<WarningOutlined />}>{value} days</Tag>
        }
        if (value <= 14) {
          return <Tag color="orange" icon={<ClockCircleOutlined />}>{value} days</Tag>
        }
        return <Tag color="gold">{value} days</Tag>
      },
      sorter: (a, b) => a.days_until_expiry - b.days_until_expiry,
    },
    {
      title: 'Status',
      key: 'status',
      render: (_, record) => {
        if (record.is_expired) return <Tag color="error">Expired</Tag>
        if (record.days_until_expiry <= 7) return <Tag color="error">Critical</Tag>
        if (record.days_until_expiry <= 14) return <Tag color="warning">Warning</Tag>
        return <Tag color="gold">Expiring Soon</Tag>
      },
    },
  ]

  const expired = (items || []).filter(i => i.is_expired).length
  const critical = (items || []).filter(i => !i.is_expired && i.days_until_expiry <= 7).length
  const warning = (items || []).filter(i => !i.is_expired && i.days_until_expiry > 7 && i.days_until_expiry <= 14).length
  const total = (items || []).length

  return (
    <>
      <Head title="Expiry Report" />

      <GlobalPageHeader
        title="Expiry Report"
        parentPageTitle="Reports"
      />

      {/* Filters */}
      <Card size="small" style={{ marginBottom: 16 }}>
        <Space>
          <span>Show products expiring within:</span>
          <Select
            value={daysAhead}
            onChange={handleDaysChange}
            style={{ width: 150 }}
          >
            <Select.Option value={7}>7 days</Select.Option>
            <Select.Option value={14}>14 days</Select.Option>
            <Select.Option value={30}>30 days</Select.Option>
            <Select.Option value={60}>60 days</Select.Option>
            <Select.Option value={90}>90 days</Select.Option>
          </Select>
        </Space>
      </Card>

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Total Items"
              value={total}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Expired"
              value={expired}
              valueStyle={{ color: '#ff4d4f' }}
              prefix={<ExclamationCircleOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Critical (≤7 days)"
              value={critical}
              valueStyle={{ color: '#ff4d4f' }}
              prefix={<WarningOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={6}>
          <Card>
            <Statistic
              title="Warning (8-14 days)"
              value={warning}
              valueStyle={{ color: '#fa8c16' }}
              prefix={<ClockCircleOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Expiring Products">
        <Table
          dataSource={items || []}
          columns={columns}
          rowKey="id"
          pagination={{ pageSize: 50, showSizeChanger: true }}
          size="small"
          rowClassName={(record) => {
            if (record.is_expired) return 'bg-red-50'
            if (record.days_until_expiry <= 7) return 'bg-orange-50'
            return ''
          }}
        />
      </Card>
    </>
  )
}
