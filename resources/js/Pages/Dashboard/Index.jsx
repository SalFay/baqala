import { useState } from 'react'
import { Head, usePage, router } from '@inertiajs/react'
import { Row, Col, Card, Statistic, Table, Tag, DatePicker, Empty, Spin } from 'antd'
import {
  ShoppingCartOutlined,
  DollarOutlined,
  RiseOutlined,
  WarningOutlined,
  UserOutlined,
} from '@ant-design/icons'
import { Column, Pie } from '@ant-design/charts'
import dayjs from 'dayjs'
import GlobalPageHeader from '@/Components/GlobalPageHeader'

const { RangePicker } = DatePicker

const ORDER_STATUS_COLORS = {
  pending: 'orange',
  completed: 'green',
  cancelled: 'red',
}

export default function Dashboard() {
  const { stats, salesChart, topProducts, recentOrders, lowStock, filters } = usePage().props
  const [dateRange, setDateRange] = useState([
    dayjs(filters?.start_date || dayjs().startOf('month')),
    dayjs(filters?.end_date || dayjs().endOf('month')),
  ])
  const [loading, setLoading] = useState(false)

  const handleDateChange = (dates) => {
    if (dates) {
      setDateRange(dates)
      setLoading(true)
      router.get(route('dashboard'), {
        start_date: dates[0].format('YYYY-MM-DD'),
        end_date: dates[1].format('YYYY-MM-DD'),
      }, {
        preserveState: true,
        onFinish: () => setLoading(false),
      })
    }
  }

  const orderColumns = [
    {
      title: 'Order #',
      dataIndex: 'order_number',
      key: 'order_number',
    },
    {
      title: 'Customer',
      dataIndex: ['customer', 'full_name'],
      key: 'customer',
      render: (text) => text || 'Walk-in',
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      render: (val) => `${Number(val || 0).toFixed(2)} SAR`,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => (
        <Tag color={ORDER_STATUS_COLORS[status] || 'default'}>
          {(status || 'unknown').toUpperCase()}
        </Tag>
      ),
    },
  ]

  const lowStockColumns = [
    {
      title: 'Product',
      dataIndex: ['product', 'name'],
      key: 'product',
    },
    {
      title: 'Current Stock',
      dataIndex: 'quantity',
      key: 'quantity',
      render: (qty) => (
        <span style={{ color: qty <= 0 ? 'red' : qty <= 10 ? 'orange' : 'inherit' }}>
          {qty}
        </span>
      ),
    },
  ]

  const salesChartConfig = {
    data: salesChart || [],
    xField: 'date',
    yField: 'total',
    style: {
      fill: '#1890ff',
      radiusTopLeft: 4,
      radiusTopRight: 4,
    },
    axis: {
      y: {
        labelFormatter: (v) => `${v} SAR`,
      },
    },
  }

  const topProductsConfig = {
    data: topProducts || [],
    angleField: 'total_qty',
    colorField: 'name',
    radius: 0.8,
    innerRadius: 0.5,
    label: {
      text: 'name',
      position: 'outside',
    },
    legend: {
      position: 'bottom',
    },
  }

  return (
    <>
      <Head title="Dashboard" />

      <Spin spinning={loading}>
        <GlobalPageHeader
          title="Dashboard"
          parentPageTitle="Home"
          parentPageRoute="dashboard"
          extraContent={
            <RangePicker
              value={dateRange}
              onChange={handleDateChange}
              allowClear={false}
            />
          }
        />

        {/* Stats Cards */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={24} sm={12} lg={6}>
            <Card>
              <Statistic
                title="Today's Sales"
                value={stats?.todaySales ?? 0}
                prefix={<DollarOutlined />}
                suffix="SAR"
                precision={2}
              />
            </Card>
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <Card>
              <Statistic
                title="Today's Orders"
                value={stats?.todayOrders ?? 0}
                prefix={<ShoppingCartOutlined />}
              />
            </Card>
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <Card>
              <Statistic
                title="Monthly Sales"
                value={stats?.monthSales ?? 0}
                prefix={<RiseOutlined />}
                suffix="SAR"
                precision={2}
              />
            </Card>
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <Card>
              <Statistic
                title="Total Customers"
                value={stats?.totalCustomers ?? 0}
                prefix={<UserOutlined />}
              />
            </Card>
          </Col>
        </Row>

        {/* Charts Row */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={24} lg={16}>
            <Card title="Sales Overview">
              {salesChart?.length > 0 ? (
                <Column {...salesChartConfig} height={300} />
              ) : (
                <Empty description="No sales data" />
              )}
            </Card>
          </Col>
          <Col xs={24} lg={8}>
            <Card title="Top Products">
              {topProducts?.length > 0 ? (
                <Pie {...topProductsConfig} height={300} />
              ) : (
                <Empty description="No product data" />
              )}
            </Card>
          </Col>
        </Row>

        {/* Tables Row */}
        <Row gutter={[16, 16]}>
          <Col xs={24} lg={12}>
            <Card title="Recent Orders">
              <Table
                dataSource={recentOrders ?? []}
                columns={orderColumns}
                rowKey="id"
                pagination={false}
                size="small"
              />
            </Card>
          </Col>
          <Col xs={24} lg={12}>
            <Card
              title={
                <span>
                  <WarningOutlined style={{ color: 'orange', marginRight: 8 }} />
                  Low Stock Items
                </span>
              }
            >
              <Table
                dataSource={lowStock ?? []}
                columns={lowStockColumns}
                rowKey="id"
                pagination={false}
                size="small"
              />
            </Card>
          </Col>
        </Row>
      </Spin>
    </>
  )
}
