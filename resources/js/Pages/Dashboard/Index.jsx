import { Head } from '@inertiajs/react';
import { Card, Row, Col, Statistic, Table } from 'antd';
import {
    ShoppingCartOutlined,
    DollarOutlined,
    TeamOutlined,
    RiseOutlined,
} from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency } from '@/Helpers/formatters';

export default function DashboardIndex({ stats = {}, recentOrders = [] }) {
    const orderColumns = [
        { title: 'Order #', dataIndex: 'order_number', key: 'order_number' },
        { title: 'Customer', dataIndex: 'customer_name', key: 'customer_name' },
        {
            title: 'Total',
            dataIndex: 'total',
            key: 'total',
            render: (total) => formatCurrency(total),
        },
        { title: 'Status', dataIndex: ['current_status', 'name'], key: 'status' },
    ];

    return (
        <MainLayout>
            <Head title="Dashboard" />

            <div style={{ marginBottom: 24 }}>
                <h1>Dashboard</h1>
            </div>

            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Today's Sales"
                            value={stats.today_sales || 0}
                            prefix={<DollarOutlined />}
                            formatter={(value) => formatCurrency(value)}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Today's Orders"
                            value={stats.today_orders || 0}
                            prefix={<ShoppingCartOutlined />}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Total Customers"
                            value={stats.total_customers || 0}
                            prefix={<TeamOutlined />}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Growth"
                            value={stats.growth || 0}
                            prefix={<RiseOutlined />}
                            suffix="%"
                        />
                    </Card>
                </Col>
            </Row>

            <Card title="Recent Orders">
                <Table
                    dataSource={recentOrders}
                    columns={orderColumns}
                    rowKey="id"
                    pagination={false}
                />
            </Card>
        </MainLayout>
    );
}
