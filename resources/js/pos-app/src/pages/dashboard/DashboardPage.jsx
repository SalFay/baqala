import { useQuery } from '@tanstack/react-query';
import { Row, Col, Card, Statistic, Table, Spin, Typography, Tag } from 'antd';
import {
  ShoppingCartOutlined,
  DollarOutlined,
  RiseOutlined,
  FallOutlined,
  InboxOutlined,
  WarningOutlined,
} from '@ant-design/icons';
import { dashboardService } from '../../api/services/dashboard.service';

const { Title } = Typography;

export default function DashboardPage() {
  const { data: stats, isLoading } = useQuery({
    queryKey: ['dashboard-stats'],
    queryFn: () => dashboardService.getStats(),
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
      render: (status) => {
        const colors = {
          completed: 'green',
          pending: 'orange',
          cancelled: 'red',
        };
        return <Tag color={colors[status] || 'default'}>{(status || 'unknown').toUpperCase()}</Tag>;
      },
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
        <span style={{ color: qty <= 0 ? 'red' : qty <= 5 ? 'orange' : 'inherit' }}>
          {qty}
        </span>
      ),
    },
    {
      title: 'Threshold',
      dataIndex: 'low_stock_threshold',
      key: 'threshold',
      render: (val) => val || 5,
    },
  ];

  if (isLoading) {
    return (
      <div style={{ textAlign: 'center', padding: 100 }}>
        <Spin size="large" />
      </div>
    );
  }

  return (
    <div>
      <Title level={4} style={{ marginBottom: 24 }}>
        Dashboard
      </Title>

      <Row gutter={[16, 16]}>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Today's Sales"
              value={stats?.today.total_sales || 0}
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
              value={stats?.today.total_orders || 0}
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

      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Month Sales"
              value={stats?.month.total_sales || 0}
              precision={2}
              suffix="SAR"
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic title="Month Orders" value={stats?.month.total_orders || 0} />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Avg. Order Value"
              value={stats?.today.average_order_value || 0}
              precision={2}
              suffix="SAR"
            />
          </Card>
        </Col>
        <Col xs={24} sm={12} lg={6}>
          <Card>
            <Statistic
              title="Inventory Value"
              value={stats?.inventory.total_value || 0}
              precision={2}
              suffix="SAR"
              prefix={<InboxOutlined />}
            />
          </Card>
        </Col>
      </Row>

      <Row gutter={[16, 16]} style={{ marginTop: 24 }}>
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
