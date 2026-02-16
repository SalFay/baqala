import { useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Row, Col, Card, Statistic, Table, Spin, Typography, Tag, DatePicker, Empty } from 'antd';
import {
  ShoppingCartOutlined,
  DollarOutlined,
  RiseOutlined,
  FallOutlined,
  InboxOutlined,
  WarningOutlined,
} from '@ant-design/icons';
import { Column, Pie } from '@ant-design/charts';
import dayjs from 'dayjs';
import { dashboardService } from '../../api/services/dashboard.service';
import { ORDER_STATUS_COLORS, DEFAULT_LOW_STOCK_THRESHOLD } from '../../constants';

const { Title } = Typography;
const { RangePicker } = DatePicker;

export default function DashboardPage() {
  const [dateRange, setDateRange] = useState([
    dayjs().startOf('month'),
    dayjs().endOf('month'),
  ]);

  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => dashboardService.getStats(),
  });

  const { data: salesChart, isLoading: salesChartLoading } = useQuery({
    queryKey: ['sales-chart', dateRange],
    queryFn: () =>
      dashboardService.getSalesChart(
        dateRange[0].format('YYYY-MM-DD'),
        dateRange[1].format('YYYY-MM-DD')
      ),
  });

  const { data: topProducts, isLoading: topProductsLoading } = useQuery({
    queryKey: ['top-products', dateRange],
    queryFn: () =>
      dashboardService.getTopProducts(
        dateRange[0].format('YYYY-MM-DD'),
        dateRange[1].format('YYYY-MM-DD')
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
      render: (val) => `${(val ?? 0).toFixed(2)} SAR`,
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
  ];

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
        <span style={{ color: qty <= 0 ? 'red' : qty <= DEFAULT_LOW_STOCK_THRESHOLD ? 'orange' : 'inherit' }}>
          {qty}
        </span>
      ),
    },
    {
      title: 'Threshold',
      dataIndex: 'low_stock_threshold',
      key: 'threshold',
      render: (val) => val || DEFAULT_LOW_STOCK_THRESHOLD,
    },
  ];

  // Sales chart config
  const salesChartConfig = {
    data: salesChart?.data || [],
    xField: 'date',
    yField: 'total',
    style: {
      fill: '#1890ff',
      radiusTopLeft: 4,
      radiusTopRight: 4,
    },
    label: {
      text: (d) => `${d.total?.toFixed(0) || 0}`,
      position: 'top',
      style: {
        fill: '#666',
        fontSize: 10,
      },
    },
    axis: {
      x: {
        label: {
          autoRotate: true,
          autoHide: true,
        },
      },
      y: {
        labelFormatter: (v) => `${v} SAR`,
      },
    },
    tooltip: {
      title: 'date',
      items: [
        { field: 'total', name: 'Sales', valueFormatter: (v) => `${v?.toFixed(2) || 0} SAR` },
        { field: 'orders', name: 'Orders' },
      ],
    },
  };

  // Top products pie chart config
  const topProductsConfig = {
    data: topProducts?.data || [],
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
    interaction: { elementHighlight: true },
    tooltip: {
      title: 'name',
      items: [
        { field: 'total_qty', name: 'Units Sold' },
        { field: 'total_sales', name: 'Sales (SAR)', valueFormatter: (v) => v?.toFixed(2) },
      ],
    },
  };

  if (isLoading) {
    return (
      <div style={{ textAlign: 'center', padding: 100 }}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <div>
      <Row justify="space-between" align="middle" style={{ marginBottom: 24 }}>
        <Col>
          <Title level={4} style={{ margin: 0 }}>
            Dashboard
          </Title>
        </Col>
        <Col>
          <RangePicker
            value={dateRange}
            onChange={(dates) => dates && setDateRange(dates)}
            allowClear={false}
          />
        </Col>
      </Row>

      {/* Stats Row 1 */}
      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Today's Sales"
              value={stats?.today?.total_sales || 0}
              precision={2}
              suffix="SAR"
              prefix={<DollarOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Today's Orders"
              value={stats?.today?.total_orders || 0}
              prefix={<ShoppingCartOutlined />}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Sales Growth"
              value={stats?.sales_growth || 0}
              precision={1}
              suffix="%"
              prefix={(stats?.sales_growth ?? 0) >= 0 ? <RiseOutlined /> : <FallOutlined />}
              valueStyle={{ color: (stats?.sales_growth ?? 0) >= 0 ? '#3f8600' : '#cf1322' }}
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Low Stock Items"
              value={stats?.inventory?.low_stock_count || 0}
              prefix={<WarningOutlined />}
              valueStyle={{ color: (stats?.inventory?.low_stock_count ?? 0) > 0 ? '#faad14' : 'inherit' }}
            />
          </Card>
        </Col>
      </Row>

      {/* Stats Row 2 */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Month Sales"
              value={stats?.month?.total_sales || 0}
              precision={2}
              suffix="SAR"
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic title="Month Orders" value={stats?.month?.total_orders || 0} />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Avg. Order Value"
              value={stats?.today?.average_order_value || 0}
              precision={2}
              suffix="SAR"
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Inventory Value"
              value={stats?.inventory?.total_value || 0}
              precision={2}
              suffix="SAR"
              prefix={<InboxOutlined />}
            />
          </Card>
        </Col>
      </Row>

      {/* Charts Row */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} lg={16}>
          <Card title="Sales Trend" loading={salesChartLoading}>
            {salesChart?.data?.length > 0 ? (
              <Column {...salesChartConfig} height={300} />
            ) : (
              <Empty description="No sales data for selected period" style={{ height: 300 }} />
            )}
          </Card>
        </Col>
        <Col xs={24} lg={8}>
          <Card title="Top Products" loading={topProductsLoading}>
            {topProducts?.data?.length > 0 ? (
              <Pie {...topProductsConfig} height={300} />
            ) : (
              <Empty description="No product data for selected period" style={{ height: 300 }} />
            )}
          </Card>
        </Col>
      </Row>

      {/* Tables Row */}
      <Row gutter={[16, 16]} style={{ marginTop: 16 }}>
        <Col xs={24} lg={12}>
          <Card title="Recent Orders">
            <Table
              dataSource={recentOrders}
              columns={orderColumns}
              rowKey="id"
              pagination={false}
              size="small"
            />
          </Card>
        </Col>
        <Col xs={24} lg={12}>
          <Card title="Low Stock Alert">
            <Table
              dataSource={lowStock}
              columns={lowStockColumns}
              rowKey="id"
              pagination={false}
              size="small"
            />
          </Card>
        </Col>
      </Row>
    </div>
  );
}
