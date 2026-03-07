import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Card, Row, Col, Table, Statistic, Typography, Avatar } from 'antd'
import { UserOutlined, DollarOutlined, ShoppingCartOutlined } from '@ant-design/icons'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import ReportFilters from '@/Components/Reports/ReportFilters'
import { formatCurrency, formatDate } from '@/Helpers/formatters'

const { Text } = Typography

export default function SalesByCustomer() {
  const { customers, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    filters?.fromDate ? dayjs(filters.fromDate) : dayjs().startOf('month'),
    filters?.toDate ? dayjs(filters.toDate) : dayjs(),
  ])

  const handleDateChange = (range) => {
    setDateRange(range)
    if (range) {
      router.get(route('reports.sales-by-customer'), {
        from_date: range[0].format('YYYY-MM-DD'),
        to_date: range[1].format('YYYY-MM-DD'),
      }, { preserveState: true })
    }
  }

  const columns = [
    {
      title: 'Customer',
      key: 'customer',
      render: (_, record) => (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
          <Avatar size="small" icon={<UserOutlined />} />
          <div>
            <Text strong>{record.customer?.name || 'Unknown'}</Text>
            {record.customer?.phone && (
              <div><Text type="secondary" style={{ fontSize: 12 }}>{record.customer.phone}</Text></div>
            )}
          </div>
        </div>
      ),
    },
    {
      title: 'Orders',
      dataIndex: 'order_count',
      key: 'order_count',
      align: 'right',
      sorter: (a, b) => a.order_count - b.order_count,
    },
    {
      title: 'Total Spent',
      dataIndex: 'total_spent',
      key: 'total_spent',
      align: 'right',
      render: (value) => formatCurrency(value),
      sorter: (a, b) => a.total_spent - b.total_spent,
      defaultSortOrder: 'descend',
    },
    {
      title: 'Avg Order',
      dataIndex: 'avg_order_value',
      key: 'avg_order_value',
      align: 'right',
      render: (value) => formatCurrency(value),
    },
    {
      title: 'Last Order',
      dataIndex: 'last_order',
      key: 'last_order',
      render: (value) => value ? formatDate(value) : '-',
    },
  ]

  const totalCustomers = (customers || []).length
  const totalRevenue = (customers || []).reduce((sum, c) => sum + parseFloat(c.total_spent || 0), 0)
  const totalOrders = (customers || []).reduce((sum, c) => sum + (c.order_count || 0), 0)

  return (
    <>
      <Head title="Sales by Customer" />

      <GlobalPageHeader
        title="Sales by Customer"
        parentPageTitle="Reports"
      />

      <ReportFilters
        dateRange={dateRange}
        onDateRangeChange={handleDateChange}
        onRefresh={() => router.reload()}
        showLocation={false}
      />

      {/* Summary */}
      <Row gutter={[16, 16]} style={{ marginBottom: 16 }}>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Total Customers"
              value={totalCustomers}
              prefix={<UserOutlined />}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Total Revenue"
              value={totalRevenue}
              prefix={<DollarOutlined />}
              precision={2}
            />
          </Card>
        </Col>
        <Col xs={12} sm={8}>
          <Card>
            <Statistic
              title="Total Orders"
              value={totalOrders}
              prefix={<ShoppingCartOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Card title="Customer Sales">
        <Table
          dataSource={customers || []}
          columns={columns}
          rowKey="customer_id"
          pagination={{ pageSize: 20, showSizeChanger: true }}
          size="small"
        />
      </Card>
    </>
  )
}
