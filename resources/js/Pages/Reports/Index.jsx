import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Statistic, DatePicker, Space, Button, Table, Typography } from 'antd';
import { DollarOutlined, ShoppingCartOutlined, TeamOutlined, DownloadOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency } from '@/Helpers/formatters';
import dayjs from 'dayjs';

const { RangePicker } = DatePicker;
const { Title } = Typography;

export default function ReportsIndex({ salesData = [], stats = {}, topProducts = [], filters = {} }) {
    const handleDateChange = (dates) => {
        if (dates) {
            router.get('/reports', {
                from_date: dates[0].format('YYYY-MM-DD'),
                to_date: dates[1].format('YYYY-MM-DD'),
            });
        }
    };

    const productColumns = [
        { title: 'Product', dataIndex: 'name', key: 'name' },
        { title: 'Qty Sold', dataIndex: 'quantity', key: 'quantity' },
        { title: 'Revenue', dataIndex: 'revenue', key: 'revenue', render: (val) => formatCurrency(val) },
    ];

    return (
        <MainLayout>
            <Head title="Reports" />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <RangePicker
                        defaultValue={[
                            dayjs(filters.from_date),
                            dayjs(filters.to_date),
                        ]}
                        onChange={handleDateChange}
                    />
                    <Button onClick={() => router.visit('/reports/sales')}>Sales Report</Button>
                    <Button onClick={() => router.visit('/reports/inventory')}>Inventory Report</Button>
                    <Button onClick={() => router.visit('/reports/customers')}>Customer Report</Button>
                    <Button icon={<DownloadOutlined />} onClick={() => window.location.href = '/reports/export?type=sales&format=csv'}>Export</Button>
                </Space>
            </div>

            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Total Sales"
                            value={stats.total_sales || 0}
                            prefix={<DollarOutlined />}
                            formatter={(value) => formatCurrency(value)}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Total Orders"
                            value={stats.total_orders || 0}
                            prefix={<ShoppingCartOutlined />}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Average Order"
                            value={stats.average_order || 0}
                            prefix={<DollarOutlined />}
                            formatter={(value) => formatCurrency(value)}
                        />
                    </Card>
                </Col>
                <Col xs={24} sm={12} lg={6}>
                    <Card>
                        <Statistic
                            title="Customers"
                            value={stats.total_customers || 0}
                            prefix={<TeamOutlined />}
                        />
                    </Card>
                </Col>
            </Row>

            <Row gutter={[16, 16]}>
                <Col xs={24} lg={12}>
                    <Card title="Top Products">
                        <Table
                            dataSource={topProducts}
                            columns={productColumns}
                            rowKey="id"
                            pagination={false}
                            size="small"
                        />
                    </Card>
                </Col>
                <Col xs={24} lg={12}>
                    <Card title="Daily Sales">
                        <Table
                            dataSource={salesData}
                            columns={[
                                { title: 'Date', dataIndex: 'date', key: 'date' },
                                { title: 'Orders', dataIndex: 'orders', key: 'orders' },
                                { title: 'Sales', dataIndex: 'total', key: 'total', render: (val) => formatCurrency(val) },
                            ]}
                            rowKey="date"
                            pagination={false}
                            size="small"
                        />
                    </Card>
                </Col>
            </Row>
        </MainLayout>
    );
}
