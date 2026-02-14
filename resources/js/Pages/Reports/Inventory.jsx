import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Table, Select, Space, Button, Statistic, Tag, Progress } from 'antd';
import { ArrowLeftOutlined, DownloadOutlined, WarningOutlined, ShopOutlined, DollarOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency, formatNumber } from '@/Helpers/formatters';

export default function ReportsInventory({ inventory = [], filters = {}, stores = [] }) {
    const handleStoreChange = (value) => {
        router.get('/reports/inventory', { store_id: value }, { preserveState: true });
    };

    const getStockStatus = (quantity, min, max) => {
        if (quantity <= 0) return { color: 'red', text: 'Out of Stock', percent: 0 };
        if (quantity <= min) return { color: 'orange', text: 'Low Stock', percent: 25 };
        if (quantity >= max) return { color: 'blue', text: 'Overstocked', percent: 100 };
        return { color: 'green', text: 'In Stock', percent: Math.min(100, Math.round((quantity / max) * 100)) };
    };

    const columns = [
        { title: 'Product', dataIndex: 'name', key: 'name' },
        { title: 'SKU', dataIndex: 'sku', key: 'sku', width: 120 },
        { title: 'Category', dataIndex: 'category', key: 'category', width: 120 },
        { title: 'In Stock', dataIndex: 'quantity', key: 'quantity', render: formatNumber, width: 100 },
        { title: 'Min', dataIndex: 'min_stock', key: 'min', render: (v) => formatNumber(v || 0), width: 80 },
        { title: 'Max', dataIndex: 'max_stock', key: 'max', render: (v) => formatNumber(v || 0), width: 80 },
        {
            title: 'Status',
            key: 'status',
            width: 120,
            render: (_, record) => {
                const status = getStockStatus(record.quantity, record.min_stock || 10, record.max_stock || 100);
                return <Tag color={status.color}>{status.text}</Tag>;
            },
        },
        {
            title: 'Stock Level',
            key: 'level',
            width: 120,
            render: (_, record) => {
                const status = getStockStatus(record.quantity, record.min_stock || 10, record.max_stock || 100);
                return <Progress percent={status.percent} size="small" showInfo={false} strokeColor={status.color === 'green' ? '#52c41a' : status.color === 'orange' ? '#faad14' : status.color === 'blue' ? '#1890ff' : '#ff4d4f'} />;
            },
        },
        { title: 'Unit Cost', dataIndex: 'cost', key: 'cost', render: formatCurrency, width: 100 },
        {
            title: 'Stock Value',
            key: 'value',
            width: 120,
            render: (_, record) => formatCurrency((record.quantity || 0) * (record.cost || 0)),
        },
    ];

    const stats = inventory.reduce((acc, item) => ({
        totalItems: acc.totalItems + (item.quantity || 0),
        totalValue: acc.totalValue + ((item.quantity || 0) * (item.cost || 0)),
        lowStock: acc.lowStock + ((item.quantity || 0) <= (item.min_stock || 10) ? 1 : 0),
        outOfStock: acc.outOfStock + ((item.quantity || 0) <= 0 ? 1 : 0),
    }), { totalItems: 0, totalValue: 0, lowStock: 0, outOfStock: 0 });

    return (
        <MainLayout>
            <Head title="Inventory Report" />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/reports')}>Back</Button>
                    {stores.length > 0 && (
                        <Select
                            value={filters.store_id}
                            onChange={handleStoreChange}
                            style={{ width: 200 }}
                            placeholder="Select Store"
                            options={stores.map(s => ({ label: s.name, value: s.id }))}
                        />
                    )}
                    <Button icon={<DownloadOutlined />} onClick={() => window.location.href = `/reports/export?type=inventory&format=csv`}>
                        Export CSV
                    </Button>
                </Space>
            </div>

            <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Products" value={inventory.length} prefix={<ShopOutlined />} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Total Items" value={stats.totalItems} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic title="Stock Value" value={stats.totalValue} prefix={<DollarOutlined />} formatter={formatCurrency} />
                    </Card>
                </Col>
                <Col xs={12} sm={6}>
                    <Card>
                        <Statistic
                            title="Low/Out of Stock"
                            value={stats.lowStock}
                            suffix={`/ ${stats.outOfStock} out`}
                            prefix={<WarningOutlined style={{ color: '#faad14' }} />}
                        />
                    </Card>
                </Col>
            </Row>

            <Card title="Inventory Report">
                <Table
                    dataSource={inventory}
                    columns={columns}
                    rowKey="id"
                    pagination={{ pageSize: 20 }}
                    scroll={{ x: 1200 }}
                />
            </Card>
        </MainLayout>
    );
}
