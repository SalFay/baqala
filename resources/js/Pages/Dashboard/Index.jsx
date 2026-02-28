import { useState, useMemo } from 'react'
import { Head, usePage, router, Link } from '@inertiajs/react'
import { Row, Col, Card, Table, Tag, DatePicker, Empty, Spin, Button, Progress, Space, Typography, theme } from 'antd'
import {
  ShoppingCartOutlined,
  DollarOutlined,
  RiseOutlined,
  FallOutlined,
  WarningOutlined,
  UserOutlined,
  PlusOutlined,
  AppstoreOutlined,
  TeamOutlined,
  FileTextOutlined,
  CalendarOutlined,
  CreditCardOutlined,
  BankOutlined,
  WalletOutlined,
} from '@ant-design/icons'
import { Column, Pie } from '@ant-design/charts'
import dayjs from 'dayjs'
import relativeTime from 'dayjs/plugin/relativeTime'
import GlobalPageHeader from '@/Components/GlobalPageHeader'
import { formatCurrency, getCurrency } from '@/Helpers/formatters'

dayjs.extend(relativeTime)

const { RangePicker } = DatePicker
const { Text, Title } = Typography

// =============================================================================
// REUSABLE COMPONENTS (DRY)
// =============================================================================

// Stat Card Component - Reusable gradient card for metrics
function StatCard({ title, value, suffix, icon, gradient, growth, subtitle }) {
  const { token } = theme.useToken()
  const gradients = {
    purple: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
    green: 'linear-gradient(135deg, #11998e 0%, #38ef7d 100%)',
    pink: 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
    orange: 'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
    blue: 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
  }

  return (
    <Card
      style={{
        background: gradients[gradient] || gradients.purple,
        borderRadius: 16,
        border: 'none',
        boxShadow: `0 4px 20px rgba(0,0,0,0.15)`,
      }}
      styles={{ body: { padding: 20 } }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
        <div>
          <Text style={{ color: 'rgba(255,255,255,0.85)', fontSize: 13 }}>{title}</Text>
          <Title level={2} style={{ color: '#fff', margin: '8px 0 4px', fontSize: 28 }}>
            {typeof value === 'number' ? value.toLocaleString() : value}
            {suffix && <Text style={{ color: 'rgba(255,255,255,0.8)', fontSize: 14, marginLeft: 4 }}>{suffix}</Text>}
          </Title>
          {growth !== undefined && <GrowthBadge value={growth} />}
          {subtitle && <Text style={{ color: 'rgba(255,255,255,0.7)', fontSize: 12 }}>{subtitle}</Text>}
        </div>
        <div style={{ fontSize: 36, opacity: 0.8, color: '#fff' }}>{icon}</div>
      </div>
    </Card>
  )
}

// Mini Stat Card - Smaller card for secondary metrics
function MiniStatCard({ title, value, icon, color, onClick }) {
  const { token } = theme.useToken()
  return (
    <Card
      style={{ borderRadius: 12, cursor: onClick ? 'pointer' : 'default' }}
      styles={{ body: { padding: 16, textAlign: 'center' } }}
      hoverable={!!onClick}
      onClick={onClick}
    >
      <div style={{ color: color || token.colorPrimary, fontSize: 24, marginBottom: 8 }}>{icon}</div>
      <Title level={3} style={{ margin: '0 0 4px', color: color }}>{value}</Title>
      <Text type="secondary" style={{ fontSize: 12 }}>{title}</Text>
    </Card>
  )
}

// Growth Badge - Shows percentage change
function GrowthBadge({ value }) {
  const isPositive = value >= 0
  return (
    <span style={{
      fontSize: 12,
      color: '#fff',
      background: isPositive ? 'rgba(82,196,26,0.3)' : 'rgba(245,34,45,0.3)',
      padding: '2px 8px',
      borderRadius: 12,
    }}>
      {isPositive ? <RiseOutlined /> : <FallOutlined />}
      {' '}{Math.abs(value).toFixed(1)}%
    </span>
  )
}

// Quick Action Button
function QuickAction({ href, icon, label, type = 'default' }) {
  return (
    <Link href={href}>
      <Button type={type} icon={icon} size="large" style={{ borderRadius: 10 }}>
        {label}
      </Button>
    </Link>
  )
}

// =============================================================================
// MAIN DASHBOARD COMPONENT
// =============================================================================

export default function Dashboard() {
  const { token } = theme.useToken()
  const {
    stats,
    salesChart,
    topProducts,
    topCategories,
    recentOrders,
    lowStock,
    ordersByStatus,
    paymentMethods,
    filters,
  } = usePage().props

  const [dateRange, setDateRange] = useState([
    dayjs(filters?.start_date),
    dayjs(filters?.end_date),
  ])
  const [loading, setLoading] = useState(false)

  // Handle date filter change
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

  // =============================================================================
  // CHART CONFIGURATIONS (KISS - Simple, readable configs)
  // =============================================================================

  const salesChartConfig = useMemo(() => ({
    data: salesChart || [],
    xField: 'date',
    yField: 'sales',
    style: {
      fill: `linear-gradient(90deg, ${token.colorPrimary} 0%, #722ed1 100%)`,
      radiusTopLeft: 6,
      radiusTopRight: 6,
    },
    axis: {
      y: { labelFormatter: (v) => `${(v / 1000).toFixed(0)}k` },
      x: { labelFormatter: (v) => dayjs(v).format('MMM D') },
    },
    tooltip: {
      title: (d) => dayjs(d.date).format('ddd, MMM D'),
      items: [{ channel: 'y', name: 'Sales', valueFormatter: (v) => formatCurrency(v) }],
    },
  }), [salesChart, token.colorPrimary])

  const topProductsConfig = useMemo(() => ({
    data: topProducts || [],
    angleField: 'total_revenue',
    colorField: 'name',
    radius: 0.85,
    innerRadius: 0.6,
    label: { text: 'name', position: 'outside', style: { fontSize: 11 } },
    legend: false,
    statistic: {
      title: { content: 'Revenue', style: { fontSize: 12, color: token.colorTextSecondary } },
      content: {
        content: formatCurrency(topProducts?.reduce((sum, p) => sum + p.total_revenue, 0) || 0),
        style: { fontSize: 16, fontWeight: 'bold' },
      },
    },
  }), [topProducts, token.colorTextSecondary])

  const paymentChartConfig = useMemo(() => ({
    data: paymentMethods || [],
    angleField: 'total',
    colorField: 'method',
    radius: 0.9,
    innerRadius: 0.5,
    label: { text: 'method', position: 'outside' },
    legend: false,
  }), [paymentMethods])

  // =============================================================================
  // TABLE COLUMNS (DRY - Reusable column definitions)
  // =============================================================================

  const orderColumns = [
    {
      title: 'Order',
      dataIndex: 'order_number',
      render: (text) => <Text strong>#{text}</Text>,
    },
    {
      title: 'Customer',
      dataIndex: 'customer_name',
      render: (text) => <Text>{text || 'Walk-in'}</Text>,
    },
    {
      title: 'Total',
      dataIndex: 'total',
      render: (val) => <Text strong style={{ color: token.colorPrimary }}>{formatCurrency(val)}</Text>,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      render: (status) => (
        <Tag color={status?.color || 'default'}>{status?.name?.toUpperCase() || 'PENDING'}</Tag>
      ),
    },
    {
      title: 'Time',
      dataIndex: 'created_at',
      render: (date) => <Text type="secondary" style={{ fontSize: 12 }}>{dayjs(date).fromNow()}</Text>,
    },
  ]

  const lowStockColumns = [
    {
      title: 'Product',
      dataIndex: 'name',
      render: (text, record) => (
        <div>
          <Text strong>{text}</Text>
          <br />
          <Text type="secondary" style={{ fontSize: 11 }}>{record.sku}</Text>
        </div>
      ),
    },
    {
      title: 'Stock',
      dataIndex: 'quantity',
      width: 130,
      render: (qty, record) => {
        const percent = Math.min(100, (qty / record.threshold) * 100)
        return (
          <Progress
            percent={percent}
            size="small"
            status={qty <= 0 ? 'exception' : 'active'}
            format={() => `${qty} / ${record.threshold}`}
            strokeColor={qty <= 0 ? token.colorError : qty <= record.threshold / 2 ? token.colorWarning : token.colorSuccess}
          />
        )
      },
    },
  ]

  // =============================================================================
  // RENDER
  // =============================================================================

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
              presets={[
                { label: 'Today', value: [dayjs(), dayjs()] },
                { label: 'This Week', value: [dayjs().startOf('week'), dayjs()] },
                { label: 'This Month', value: [dayjs().startOf('month'), dayjs()] },
                { label: 'Last 30 Days', value: [dayjs().subtract(30, 'day'), dayjs()] },
              ]}
            />
          }
        />

        {/* Main Stats Row */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={24} sm={12} lg={6}>
            <StatCard
              title="Today's Sales"
              value={stats?.todaySales?.toFixed(2)}
              suffix={getCurrency()}
              icon={<DollarOutlined />}
              gradient="purple"
              growth={stats?.dailyGrowth}
            />
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <StatCard
              title="Today's Orders"
              value={stats?.todayOrders}
              icon={<ShoppingCartOutlined />}
              gradient="green"
              subtitle="transactions"
            />
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <StatCard
              title="Monthly Revenue"
              value={stats?.monthSales?.toFixed(0)}
              suffix={getCurrency()}
              icon={<CalendarOutlined />}
              gradient="pink"
              growth={stats?.monthlyGrowth}
            />
          </Col>
          <Col xs={24} sm={12} lg={6}>
            <StatCard
              title="Total Customers"
              value={stats?.totalCustomers}
              icon={<TeamOutlined />}
              gradient="orange"
              subtitle="registered"
            />
          </Col>
        </Row>

        {/* Quick Actions */}
        <Card size="small" style={{ borderRadius: 12, marginBottom: 24 }}>
          <Space wrap>
            <QuickAction href={route('pos')} icon={<PlusOutlined />} label="New Sale" type="primary" />
            <QuickAction href={route('products.page')} icon={<AppstoreOutlined />} label="Products" />
            <QuickAction href={route('customers.page')} icon={<TeamOutlined />} label="Customers" />
            <QuickAction href={route('orders.page')} icon={<FileTextOutlined />} label="Orders" />
          </Space>
        </Card>

        {/* Secondary Metrics */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={12} sm={6}>
            <MiniStatCard
              title="Avg Order Value"
              value={formatCurrency(stats?.avgOrderValue || 0)}
              icon={<DollarOutlined />}
              color={token.colorPrimary}
            />
          </Col>
          <Col xs={12} sm={6}>
            <MiniStatCard
              title="Week Sales"
              value={formatCurrency(stats?.weekSales || 0)}
              icon={<CalendarOutlined />}
              color="#722ed1"
            />
          </Col>
          <Col xs={12} sm={6}>
            <MiniStatCard
              title="Low Stock"
              value={stats?.lowStockCount || 0}
              icon={<WarningOutlined />}
              color={token.colorWarning}
              onClick={() => router.visit(route('products.page'))}
            />
          </Col>
          <Col xs={12} sm={6}>
            <MiniStatCard
              title="Out of Stock"
              value={stats?.outOfStockCount || 0}
              icon={<WarningOutlined />}
              color={token.colorError}
              onClick={() => router.visit(route('products.page'))}
            />
          </Col>
        </Row>

        {/* Charts Row */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={24} lg={16}>
            <Card title="Sales Trend" style={{ borderRadius: 12 }}>
              {salesChart?.length > 0 ? (
                <Column {...salesChartConfig} height={280} />
              ) : (
                <Empty description="No sales data" style={{ padding: 40 }} />
              )}
            </Card>
          </Col>
          <Col xs={24} lg={8}>
            <Card title="Top Products" style={{ borderRadius: 12 }}>
              {topProducts?.length > 0 ? (
                <Pie {...topProductsConfig} height={280} />
              ) : (
                <Empty description="No data" style={{ padding: 40 }} />
              )}
            </Card>
          </Col>
        </Row>

        {/* Order Status & Payment Methods */}
        <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
          <Col xs={24} sm={12}>
            <Card title="Orders by Status" style={{ borderRadius: 12 }}>
              <Space wrap style={{ width: '100%' }}>
                {(ordersByStatus || []).map((item, i) => (
                  <Tag
                    key={i}
                    color={item.color}
                    style={{ padding: '4px 12px', fontSize: 14, borderRadius: 8 }}
                  >
                    {item.status}: {item.count}
                  </Tag>
                ))}
              </Space>
              {(!ordersByStatus || ordersByStatus.length === 0) && (
                <Empty description="No orders" image={Empty.PRESENTED_IMAGE_SIMPLE} />
              )}
            </Card>
          </Col>
          <Col xs={24} sm={12}>
            <Card title="Payment Methods" style={{ borderRadius: 12 }}>
              {paymentMethods?.length > 0 ? (
                <Pie {...paymentChartConfig} height={150} />
              ) : (
                <Empty description="No data" image={Empty.PRESENTED_IMAGE_SIMPLE} />
              )}
            </Card>
          </Col>
        </Row>

        {/* Tables Row */}
        <Row gutter={[16, 16]}>
          <Col xs={24} lg={14}>
            <Card
              title="Recent Orders"
              style={{ borderRadius: 12 }}
              extra={<Link href={route('orders.page')}>View All</Link>}
            >
              <Table
                dataSource={recentOrders || []}
                columns={orderColumns}
                rowKey="id"
                pagination={false}
                size="small"
              />
            </Card>
          </Col>
          <Col xs={24} lg={10}>
            <Card
              title={<><WarningOutlined style={{ color: token.colorWarning, marginRight: 8 }} />Low Stock Alerts</>}
              style={{ borderRadius: 12 }}
              extra={<Link href={route('products.page')}>View All</Link>}
            >
              <Table
                dataSource={lowStock || []}
                columns={lowStockColumns}
                rowKey="id"
                pagination={false}
                size="small"
                locale={{ emptyText: <Empty description="All products well stocked" image={Empty.PRESENTED_IMAGE_SIMPLE} /> }}
              />
            </Card>
          </Col>
        </Row>
      </Spin>
    </>
  )
}
