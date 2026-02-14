import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Table, DatePicker, Space, Button, Select, Statistic } from 'antd';
import { ArrowLeftOutlined, DownloadOutlined, DollarOutlined, ShoppingCartOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency, formatNumber } from '@/Helpers/formatters';
import dayjs from 'dayjs';

const { RangePicker } = DatePicker;

export default function ReportsSales({ salesData = [], filters = {} }) {
    const handleDateChange = (dates) => {
        if (dates) {
            router.get('/reports/sales', {
                ...filters,
                from_date: dates[0].format('YYYY-MM-DD'),
                to_date: dates[1].format('YYYY-MM-DD'),
            }, { preserveState: true });
        }
    };

    const handleGroupByChange = (value) => {
        router.get('/reports/sales', { ...filters, group_by: value }, { preserveState: true });
    };

    const columns = [
        { title: 'Period', dataIndex: 'period', key: 'period' },
        { title: 'Orders', dataIndex: 'orders', key: 'orders', render: formatNumber },
        { title: 'Items Sold', dataIndex: 'items', key: 'items', render: formatNumber },
        { title: 'Gross Sales', dataIndex: 'gross_sales', key: 'gross', render: formatCurrency },
        { title: 'Discounts', dataIndex: 'discounts', key: 'discounts', render: formatCurrency },
        { title: 'Tax', dataIndex: 'tax', key: 'tax', render: formatCurrency },
        { title: 'Net Sales', dataIndex: 'net_sales', key: 'net', render: formatCurrency },
    ];

    const totals = salesData.reduce((acc, row) => ({
        orders: acc.orders + (row.orders || 0),
        items: acc.items + (row.items || 0),
        gross_sales: acc.gross_sales + (row.gross_sales || 0),
        discounts: acc.discounts + (row.discounts || 0),
        tax: acc.tax + (row.tax || 0),
        net_sales: acc.net_sales + (row.net_sales || 0),
    }), { orders: 0, items: 0, gross_sales: 0, discounts: 0, tax: 0, net_sales: 0 });

    return (
        <MainLayout>
            <Head title="Sales Report" />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/reports')}>Back</Button>
                    <RangePicker
                        defaultValue={[dayjs(filters.fromDate), dayjs(filters.toDate)]}
                        onChange={handleDateChange}
                    />
                    <Select
                        value={filters.groupBy || 'day'}
                        onChange={handleGroupByChange}
                        style={{ width: 120 }}
                        options={[
                            { label: 'Daily', value: 'day' },
                            { label: 'Weekly', value: 'week' },
                            { label: 'Monthly', value: 'month' },
                        ]}
                    />
                    <Button icon={<DownloadOutlined />} onClick={() => window.location.href = `/reports/export?type=sales&from_date=${filters.fromDate}&to_date=${filters.toDate}`}>
                        Export CSV
                    </Button>
                </Space>
            </div>

            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Orders" value={totals.orders} prefix={<ShoppingCartOutlined />} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Items Sold" value={totals.items} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Gross Sales" value={totals.gross_sales} prefix={<DollarOutlined />} formatter={formatCurrency} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Net Sales" value={totals.net_sales} prefix={<DollarOutlined />} formatter={formatCurrency} />
                    </Card>
                </Col>
            </Row>

            <Card title="Sales Report">
                <Table
                    dataSource={salesData}
                    columns={columns}
                    rowKey="period"
                    pagination={false}
                    scroll={{ x: 800 }}
                    summary={() => (
                        <Table.Summary>
                            <Table.Summary.Row>
                                <Table.Summary.Cell><strong>Total</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatNumber(totals.orders)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatNumber(totals.items)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatCurrency(totals.gross_sales)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatCurrency(totals.discounts)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatCurrency(totals.tax)}</strong></Table.Summary.Cell>
                                <Table.Summary.Cell><strong>{formatCurrency(totals.net_sales)}</strong></Table.Summary.Cell>
                            </Table.Summary.Row>
                        </Table.Summary>
                    )}
                />
            </Card>
        </MainLayout>
    );
}
