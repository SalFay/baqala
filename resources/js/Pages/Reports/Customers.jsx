import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Table, DatePicker, Space, Button, Statistic, Tag } from 'antd';
import { ArrowLeftOutlined, DownloadOutlined, TeamOutlined, DollarOutlined, ShoppingCartOutlined, StarOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency, formatNumber, formatDateTime } from '@/Helpers/formatters';
import dayjs from 'dayjs';

const { RangePicker } = DatePicker;

export default function ReportsCustomers({ customers = [], filters = {} }) {
    const handleDateChange = (dates) => {
        if (dates) {
            router.get('/reports/customers', {
                from_date: dates[0].format('YYYY-MM-DD'),
                to_date: dates[1].format('YYYY-MM-DD'),
            }, { preserveState: true });
        }
    };

    const columns = [
        { title: 'Customer', dataIndex: 'name', key: 'name' },
        { title: 'Email', dataIndex: 'email', key: 'email' },
        { title: 'Phone', dataIndex: 'phone', key: 'phone' },
        { title: 'Total Orders', dataIndex: 'total_orders', key: 'orders', render: formatNumber },
        { title: 'Total Spent', dataIndex: 'total_spent', key: 'spent', render: formatCurrency },
        {
            title: 'Avg Order',
            key: 'avg',
            render: (_, record) => formatCurrency(record.total_orders > 0 ? record.total_spent / record.total_orders : 0),
        },
        { title: 'Last Order', dataIndex: 'last_order_at', key: 'last', render: formatDateTime },
        {
            title: 'Loyalty Points',
            dataIndex: 'loyalty_points',
            key: 'loyalty',
            render: (points) => (
                <Space>
                    <StarOutlined style={{ color: '#faad14' }} />
                    {formatNumber(points || 0)}
                </Space>
            ),
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            render: (status) => <Tag color={status === 'active' ? 'green' : 'default'}>{status}</Tag>,
        },
    ];

    const stats = customers.reduce((acc, customer) => ({
        totalCustomers: acc.totalCustomers + 1,
        totalOrders: acc.totalOrders + (customer.total_orders || 0),
        totalRevenue: acc.totalRevenue + (customer.total_spent || 0),
        activeCustomers: acc.activeCustomers + (customer.total_orders > 0 ? 1 : 0),
    }), { totalCustomers: 0, totalOrders: 0, totalRevenue: 0, activeCustomers: 0 });

    return (
        <MainLayout>
            <Head title="Customer Report" />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/reports')}>Back</Button>
                    <RangePicker
                        defaultValue={[dayjs(filters.fromDate), dayjs(filters.toDate)]}
                        onChange={handleDateChange}
                    />
                    <Button icon={<DownloadOutlined />} onClick={() => window.location.href = `/reports/export?type=customers&from_date=${filters.fromDate}&to_date=${filters.toDate}`}>
                        Export CSV
                    </Button>
                </Space>
            </div>

            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Customers" value={stats.totalCustomers} prefix={<TeamOutlined />} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Active Customers" value={stats.activeCustomers} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Orders" value={stats.totalOrders} prefix={<ShoppingCartOutlined />} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Revenue" value={stats.totalRevenue} prefix={<DollarOutlined />} formatter={formatCurrency} />
                    </Card>
                </Col>
            </Row>

            <Card title="Customer Report">
                <Table
                    dataSource={customers}
                    columns={columns}
                    rowKey="id"
                    pagination={{ pageSize: 20 }}
                    scroll={{ x: 1100 }}
                />
            </Card>
        </MainLayout>
    );
}
