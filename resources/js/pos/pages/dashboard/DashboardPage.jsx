import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import {
  Row,
  Col,
  Card,
  Statistic,
  Table,
  Spin,
  Typography,
  Tag,
  DatePicker,
  Space,
  Progress,
  Avatar,
  Flex,
  theme,
  Segmented,
  Badge,
  Tooltip,
} from 'antd';
import {
  ShoppingCartOutlined,
  DollarOutlined,
  RiseOutlined,
  FallOutlined,
  WarningOutlined,
  UserOutlined,
  ShoppingOutlined,
  CalendarOutlined,
  EyeOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  SyncOutlined,
  CreditCardOutlined,
  WalletOutlined,
  BankOutlined,
} from '@ant-design/icons';
import { Line, Column, Pie } from '@ant-design/charts';
import { dayjs, DATE_FORMAT, DATE_TIME_FORMAT } from '@pos/Helpers/atom';
import { dashboardService } from '@pos/api/services/dashboard.service';

const { Title, Text } = Typography;
const { RangePicker } = DatePicker;
const { useToken } = theme;

// Stat card with gradient background
function StatCard({ title, value, prefix, suffix, trend, trendValue, icon, color, onClick }) {
  const { token } = useToken();
  const isPositive = trend === 'up';

  return (
    <Card
      hoverable={!!onClick}
      onClick={onClick}
      styles={{
        body: { padding: 20 },
      }}
      style={{
        borderRadius: 12,
        border: 'none',
        boxShadow: token.boxShadowTertiary,
      }}
    >
      <Flex justify="space-between" align="flex-start">
        <div>
          <Text type="secondary" style={{ fontSize: 13, display: 'block', marginBottom: 8 }}>
            {title}
          </Text>
          <Statistic
            value={value}
            prefix={prefix}
            suffix={suffix}
            valueStyle={{ fontSize: 28, fontWeight: 600 }}
          />
          {trend && (
            <Flex align="center" gap={4} style={{ marginTop: 8 }}>
              {isPositive ? (
                <RiseOutlined style={{ color: token.colorSuccess, fontSize: 12 }} />
              ) : (
                <FallOutlined style={{ color: token.colorError, fontSize: 12 }} />
              )}
              <Text
                style={{
                  fontSize: 12,
                  color: isPositive ? token.colorSuccess : token.colorError,
                  fontWeight: 500,
                }}
              >
                {trendValue}%
              </Text>
              <Text type="secondary" style={{ fontSize: 12 }}>vs yesterday</Text>
            </Flex>
          )}
        </div>
        <Avatar
          size={48}
          style={{
            backgroundColor: `${color}15`,
            color: color,
          }}
          icon={icon}
        />
      </Flex>
    </Card>
  );
}

// Mini stat for secondary metrics
function MiniStat({ label, value, icon, color }) {
  const { token } = useToken();

  return (
    <Flex align="center" gap={12} style={{ padding: '12px 0' }}>
      <Avatar
        size={36}
        style={{ backgroundColor: `${color}15`, color: color }}
        icon={icon}
      />
      <div>
        <Text type="secondary" style={{ fontSize: 12, display: 'block' }}>{label}</Text>
        <Text strong style={{ fontSize: 16 }}>{value}</Text>
      </div>
    </Flex>
  );
}

export default function DashboardPage() {
  const { token } = useToken();
  const [dateRange, setDateRange] = useState([
    dayjs().subtract(7, 'day'),
    dayjs(),
  ]);
  const [chartPeriod, setChartPeriod] = useState('7d');

  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => dashboardService.getStats(),
  });

  const { data: salesChart } = useQuery({
    queryKey: ['sales-chart', dateRange[0]?.format('YYYY-MM-DD'), dateRange[1]?.format('YYYY-MM-DD')],
    queryFn: () =>
      dashboardService.getSalesChart(
        dateRange[0]?.format('YYYY-MM-DD'),
        dateRange[1]?.format('YYYY-MM-DD')
      ),
  });

  const { data: topProducts } = useQuery({
    queryKey: ['top-products', dateRange[0]?.format('YYYY-MM-DD'), dateRange[1]?.format('YYYY-MM-DD')],
    queryFn: () =>
      dashboardService.getTopProducts(
        dateRange[0]?.format('YYYY-MM-DD'),
        dateRange[1]?.format('YYYY-MM-DD'),
        null,
        5
      ),
  });

  const { data: recentOrders } = useQuery({
    queryKey: ['recent-orders'],
    queryFn: () => dashboardService.getRecentOrders(),
  });

  const { data: lowStock } = useQuery({
    queryKey: ['low-stock'],
    queryFn: () => dashboardService.getLowStock(),
  });

  // Handle chart period change
  const handlePeriodChange = (period) => {
    setChartPeriod(period);
    const periodMap = {
      '7d': 7,
      '30d': 30,
      '90d': 90,
    };
    setDateRange([dayjs().subtract(periodMap[period], 'day'), dayjs()]);
  };

  // Chart configurations
  const salesChartConfig = {
    data: salesChart?.data || [],
    xField: 'date',
    yField: 'total',
    smooth: true,
    height: 280,
    color: token.colorPrimary,
    lineStyle: { lineWidth: 3 },
    point: {
      size: 6,
      shape: 'circle',
      style: { fill: token.colorPrimary, stroke: '#fff', lineWidth: 2 },
    },
    areaStyle: {
      fill: `l(270) 0:${token.colorPrimary}10 1:${token.colorPrimary}40`,
    },
    label: {
      content: (datum) => datum.total ? `${Number(datum.total).toLocaleString()}` : '',
      style: { fill: token.colorText, fontSize: 11, fontWeight: 600 },
      offsetY: -12,
    },
    xAxis: {
      label: {
        formatter: (v) => dayjs(v).format(DATE_FORMAT),
        style: { fill: token.colorText, fontSize: 12 },
      },
      line: { style: { stroke: token.colorBorder } },
      tickLine: { style: { stroke: token.colorBorder } },
    },
    yAxis: {
      label: {
        formatter: (v) => {
          const num = Number(v);
          if (num >= 1000) return `${(num / 1000).toFixed(1)}K`;
          return num.toLocaleString();
        },
        style: { fill: token.colorText, fontSize: 12 },
      },
      grid: { line: { style: { stroke: token.colorBorder, lineDash: [4, 4] } } },
      line: { style: { stroke: token.colorBorder } },
    },
    tooltip: {
      formatter: (datum) => ({
        name: 'Revenue',
        value: `${Number(datum.total).toLocaleString()} SAR`,
      }),
      customContent: (title, items) => {
        if (!items?.length) return '';
        const data = items[0]?.data || {};
        return `
          <div style="padding: 8px 12px;">
            <div style="font-weight: 600; margin-bottom: 4px;">${dayjs(data.date).format(DATE_FORMAT)}</div>
            <div style="color: ${token.colorPrimary};">Revenue: ${Number(data.total).toLocaleString()} SAR</div>
            <div style="color: ${token.colorTextSecondary}; font-size: 12px;">Orders: ${data.orders || 0}</div>
          </div>
        `;
      },
    },
  };

  const topProductsConfig = {
    data: topProducts?.data || [],
    xField: 'name',
    yField: 'total_qty',
    height: 280,
    color: token.colorSuccess,
    columnStyle: { radius: [4, 4, 0, 0] },
    label: {
      position: 'top',
      content: (datum) => `${datum.total_qty || ''}`,
      style: { fill: token.colorText, fontSize: 12, fontWeight: 600 },
    },
    xAxis: {
      label: {
        autoRotate: true,
        autoHide: false,
        style: { fill: token.colorText, fontSize: 12 },
      },
      line: { style: { stroke: token.colorBorder } },
      tickLine: { style: { stroke: token.colorBorder } },
    },
    yAxis: {
      label: {
        style: { fill: token.colorText, fontSize: 12 },
        formatter: (v) => Number(v).toLocaleString(),
      },
      grid: { line: { style: { stroke: token.colorBorder, lineDash: [4, 4] } } },
      line: { style: { stroke: token.colorBorder } },
    },
    tooltip: {
      customContent: (title, items) => {
        if (!items?.length) return '';
        const data = items[0]?.data || {};
        return `
          <div style="padding: 8px 12px;">
            <div style="font-weight: 600; margin-bottom: 4px;">${data.name}</div>
            <div style="color: ${token.colorSuccess};">Quantity: ${data.total_qty} units</div>
            <div style="color: ${token.colorPrimary};">Revenue: ${Number(data.total_sales || 0).toLocaleString()} SAR</div>
          </div>
        `;
      },
    },
  };

  // Payment methods pie chart data (mock - can be real from API)
  const paymentData = [
    { type: 'Cash', value: 45 },
    { type: 'Card', value: 35 },
    { type: 'Bank Transfer', value: 20 },
  ];

  const paymentPieConfig = {
    data: paymentData,
    angleField: 'value',
    colorField: 'type',
    height: 160,
    radius: 0.8,
    innerRadius: 0.6,
    color: [token.colorSuccess, token.colorPrimary, token.colorWarning],
    label: false,
    legend: {
      position: 'right',
      itemName: { style: { fill: token.colorTextSecondary, fontSize: 12 } },
    },
    statistic: {
      title: false,
      content: {
        style: { fontSize: 14, fontWeight: 600, color: token.colorText },
        content: 'Payments',
      },
    },
  };

  const orderColumns = [
    {
      title: 'Order',
      dataIndex: 'order_number',
      key: 'order_number',
      render: (text) => (
        <Text strong style={{ color: token.colorPrimary }}>{text}</Text>
      ),
    },
    {
      title: 'Customer',
      dataIndex: ['customer', 'full_name'],
      key: 'customer',
      render: (text) => (
        <Flex align="center" gap={8}>
          <Avatar size={24} style={{ backgroundColor: token.colorPrimaryBg }}>
            <UserOutlined style={{ fontSize: 12, color: token.colorPrimary }} />
          </Avatar>
          <Text>{text || 'Walk-in'}</Text>
        </Flex>
      ),
    },
    {
      title: 'Amount',
      dataIndex: 'total',
      key: 'total',
      render: (val) => <Text strong>{Number(val || 0).toFixed(2)} SAR</Text>,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (status) => {
        const config = {
          completed: { color: 'success', icon: <CheckCircleOutlined /> },
          pending: { color: 'warning', icon: <ClockCircleOutlined /> },
          processing: { color: 'processing', icon: <SyncOutlined spin /> },
          cancelled: { color: 'error', icon: null },
        };
        const { color, icon } = config[status] || { color: 'default', icon: null };
        return (
          <Tag color={color} icon={icon}>
            {(status || 'unknown').charAt(0).toUpperCase() + (status || 'unknown').slice(1)}
          </Tag>
        );
      },
    },
    {
      title: 'Time',
      dataIndex: 'created_at',
      key: 'created_at',
      render: (date) => (
        <Tooltip title={dayjs(date).format(DATE_TIME_FORMAT)}>
          <Text type="secondary" style={{ fontSize: 12 }}>
            {dayjs(date).fromNow()}
          </Text>
        </Tooltip>
      ),
    },
  ];

  const lowStockColumns = [
    {
      title: 'Product',
      dataIndex: ['product', 'name'],
      key: 'product',
      render: (text, record) => (
        <Flex align="center" gap={8}>
          <Avatar
            size={32}
            src={record.product?.image_url}
            style={{ backgroundColor: token.colorBgContainerDisabled }}
          >
            {text?.[0]}
          </Avatar>
          <Text ellipsis style={{ maxWidth: 120 }}>{text}</Text>
        </Flex>
      ),
    },
    {
      title: 'Stock',
      dataIndex: 'quantity',
      key: 'quantity',
      render: (qty, record) => {
        const threshold = record.low_stock_threshold || 10;
        const percent = Math.min((qty / threshold) * 100, 100);
        const status = qty <= 0 ? 'exception' : qty <= threshold / 2 ? 'normal' : 'active';
        return (
          <Flex align="center" gap={8}>
            <Progress
              percent={percent}
              size="small"
              status={status}
              showInfo={false}
              style={{ width: 50 }}
              strokeColor={qty <= 0 ? token.colorError : qty <= 5 ? token.colorWarning : token.colorSuccess}
            />
            <Badge
              count={qty}
              showZero
              color={qty <= 0 ? token.colorError : qty <= 5 ? token.colorWarning : token.colorTextSecondary}
              style={{ fontSize: 11 }}
            />
          </Flex>
        );
      },
    },
    {
      title: 'Action',
      key: 'action',
      render: () => (
        <Tag color="blue" style={{ cursor: 'pointer' }}>Restock</Tag>
      ),
    },
  ];

  if (isLoading) {
    return (
      <Flex justify="center" align="center" style={{ height: 400 }}>
        <Spin size="large" />
      </Flex>
    );
  }

  const growth = stats?.sales_growth ?? 0;
  const isPositiveGrowth = growth >= 0;

  return (
    <div>
      {/* Header */}
      <Flex justify="space-between" align="center" wrap="wrap" gap={16} style={{ marginBottom: 24 }}>
        <div>
          <Title level={4} style={{ margin: 0 }}>Dashboard</Title>
          <Text type="secondary">Welcome back! Here's what's happening today.</Text>
        </div>
        <Space wrap>
          <RangePicker
            value={dateRange}
            onChange={(dates) => setDateRange(dates || [dayjs().subtract(7, 'day'), dayjs()])}
            presets={[
              { label: 'Today', value: [dayjs(), dayjs()] },
              { label: 'Yesterday', value: [dayjs().subtract(1, 'day'), dayjs().subtract(1, 'day')] },
              { label: 'Last 7 Days', value: [dayjs().subtract(7, 'day'), dayjs()] },
              { label: 'Last 30 Days', value: [dayjs().subtract(30, 'day'), dayjs()] },
              { label: 'This Month', value: [dayjs().startOf('month'), dayjs()] },
            ]}
            suffixIcon={<CalendarOutlined />}
          />
        </Space>
      </Flex>

      {/* Main Stats */}
      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} lg={6}>
          <StatCard
            title="Total Revenue"
            value={stats?.today.total_sales || 0}
            suffix=" SAR"
            trend={isPositiveGrowth ? 'up' : 'down'}
            trendValue={Math.abs(growth).toFixed(1)}
            icon={<DollarOutlined />}
            color={token.colorPrimary}
          />
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <StatCard
            title="Orders"
            value={stats?.today.total_orders || 0}
            icon={<ShoppingCartOutlined />}
            color={token.colorSuccess}
          />
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <StatCard
            title="Avg. Order Value"
            value={stats?.today.average_order_value || 0}
            suffix=" SAR"
            icon={<ShoppingOutlined />}
            color={token.colorWarning}
          />
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <StatCard
            title="Low Stock Items"
            value={stats?.inventory?.low_stock_count || 0}
            icon={<WarningOutlined />}
            color={(stats?.inventory?.low_stock_count ?? 0) > 0 ? token.colorError : token.colorSuccess}
          />
        </Col>
      </Row>

      {/* Charts Row */}
      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
        <Col xs={24} lg={16}>
          <Card
            title={
              <Flex justify="space-between" align="center">
                <Space>
                  <DollarOutlined />
                  <span>Revenue Overview</span>
                </Space>
                <Segmented
                  size="small"
                  options={[
                    { label: '7D', value: '7d' },
                    { label: '30D', value: '30d' },
                    { label: '90D', value: '90d' },
                  ]}
                  value={chartPeriod}
                  onChange={handlePeriodChange}
                />
              </Flex>
            }
            styles={{ body: { padding: '12px 24px 24px' } }}
            style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}
          >
            {salesChart?.data?.length > 0 ? (
              <Line {...salesChartConfig} />
            ) : (
              <Flex justify="center" align="center" style={{ height: 280 }}>
                <Text type="secondary">No sales data for selected period</Text>
              </Flex>
            )}
          </Card>
        </Col>
        <Col xs={24} lg={8}>
          <Card
            title={<Space><CreditCardOutlined /> Payment Methods</Space>}
            styles={{ body: { padding: '12px 16px' } }}
            style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary, height: '100%' }}
          >
            <Pie {...paymentPieConfig} />
            <div style={{ marginTop: 16 }}>
              <MiniStat
                label="Cash Payments"
                value={`${Number(stats?.month.total_sales * 0.45 || 0).toLocaleString()} SAR`}
                icon={<WalletOutlined />}
                color={token.colorSuccess}
              />
              <MiniStat
                label="Card Payments"
                value={`${Number(stats?.month.total_sales * 0.35 || 0).toLocaleString()} SAR`}
                icon={<CreditCardOutlined />}
                color={token.colorPrimary}
              />
              <MiniStat
                label="Bank Transfer"
                value={`${Number(stats?.month.total_sales * 0.20 || 0).toLocaleString()} SAR`}
                icon={<BankOutlined />}
                color={token.colorWarning}
              />
            </div>
          </Card>
        </Col>
      </Row>

      {/* Second Charts Row */}
      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
        <Col xs={24} lg={12}>
          <Card
            title={<Space><ShoppingOutlined /> Top Selling Products</Space>}
            styles={{ body: { padding: '12px 24px 24px' } }}
            style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}
          >
            {topProducts?.data?.length > 0 ? (
              <Column {...topProductsConfig} />
            ) : (
              <Flex justify="center" align="center" style={{ height: 280 }}>
                <Text type="secondary">No product data for selected period</Text>
              </Flex>
            )}
          </Card>
        </Col>
        <Col xs={24} lg={12}>
          <Card
            title={
              <Flex justify="space-between" align="center">
                <Space>
                  <WarningOutlined style={{ color: token.colorWarning }} />
                  <span>Low Stock Alerts</span>
                </Space>
                {(stats?.inventory?.low_stock_count ?? 0) > 0 && (
                  <Badge
                    count={stats?.inventory?.low_stock_count}
                    style={{ backgroundColor: token.colorWarning }}
                  />
                )}
              </Flex>
            }
            styles={{ body: { padding: 0 } }}
            style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}
          >
            <Table
              dataSource={lowStock?.slice(0, 5)}
              columns={lowStockColumns}
              rowKey="id"
              pagination={false}
              size="small"
              locale={{ emptyText: <Text type="success" style={{ padding: 20, display: 'block' }}>All items well stocked!</Text> }}
            />
          </Card>
        </Col>
      </Row>

      {/* Recent Orders */}
      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
        <Col xs={24}>
          <Card
            title={
              <Flex justify="space-between" align="center">
                <Space>
                  <ShoppingCartOutlined />
                  <span>Recent Orders</span>
                </Space>
                <Tag color="blue" style={{ cursor: 'pointer' }}>
                  <EyeOutlined /> View All
                </Tag>
              </Flex>
            }
            styles={{ body: { padding: 0 } }}
            style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}
          >
            <Table
              dataSource={recentOrders}
              columns={orderColumns}
              rowKey="id"
              pagination={false}
              size="middle"
            />
          </Card>
        </Col>
      </Row>

      {/* Summary Cards */}
      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
        <Col xs={24} sm={8}>
          <Card style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}>
            <Statistic
              title="Month Sales"
              value={stats?.month.total_sales || 0}
              precision={2}
              suffix="SAR"
              valueStyle={{ color: token.colorPrimary }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={8}>
          <Card style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}>
            <Statistic
              title="Month Orders"
              value={stats?.month.total_orders || 0}
              valueStyle={{ color: token.colorSuccess }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={8}>
          <Card style={{ borderRadius: 12, boxShadow: token.boxShadowTertiary }}>
            <Statistic
              title="Inventory Value"
              value={stats?.inventory?.total_value || 0}
              precision={0}
              suffix="SAR"
              valueStyle={{ color: token.colorWarning }}
            />
          </Card>
        </Col>
      </Row>
    </div>
  );
}
