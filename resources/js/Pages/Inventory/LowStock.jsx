import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, Table, Input, Select, Space, Button, Tag, Progress, message } from 'antd';
import { ArrowLeftOutlined, SearchOutlined, ReloadOutlined, ShoppingCartOutlined, WarningOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatNumber } from '@/Helpers/formatters';

export default function InventoryLowStock({ products = { data: [], meta: {} }, filters = {}, categories = [], stores = [] }) {
    const [loading, setLoading] = useState(false);
    const [selectedProducts, setSelectedProducts] = useState([]);

    const handleFilter = (newFilters) => {
        router.get('/inventory/low-stock', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const getStockLevel = (current, min, max) => {
        if (current <= 0) return { status: 'exception', percent: 0, label: 'Out of Stock' };
        if (current <= min) return { status: 'exception', percent: Math.round((current / max) * 100), label: 'Critical' };
        if (current <= min * 2) return { status: 'active', percent: Math.round((current / max) * 100), label: 'Low' };
        return { status: 'normal', percent: Math.round((current / max) * 100), label: 'OK' };
    };

    const createPurchaseOrder = () => {
        if (selectedProducts.length === 0) {
            message.warning('Select products to create a purchase order');
            return;
        }
        // Navigate to PO create with pre-selected products
        router.visit('/purchase-orders/create', {
            data: { products: selectedProducts },
        });
    };

    const columns = [
        { title: 'Product', dataIndex: 'name', key: 'name' },
        { title: 'SKU', dataIndex: 'sku', key: 'sku', width: 120 },
        {
            title: 'Category',
            dataIndex: ['category', 'name'],
            key: 'category',
            width: 120,
            render: (cat) => cat || '-',
        },
        {
            title: 'Current Stock',
            dataIndex: 'stock_quantity',
            key: 'stock',
            width: 120,
            render: (qty, record) => {
                const level = getStockLevel(qty, record.min_stock || 10, record.max_stock || 100);
                return (
                    <Space direction="vertical" size={0}>
                        <span style={{ fontWeight: 500 }}>{formatNumber(qty)}</span>
                        <Tag color={level.status === 'exception' ? 'red' : level.status === 'active' ? 'orange' : 'green'}>
                            {level.label}
                        </Tag>
                    </Space>
                );
            },
        },
        {
            title: 'Min Stock',
            dataIndex: 'min_stock',
            key: 'min',
            width: 100,
            render: (v) => formatNumber(v || 10),
        },
        {
            title: 'Stock Level',
            key: 'level',
            width: 150,
            render: (_, record) => {
                const level = getStockLevel(record.stock_quantity, record.min_stock || 10, record.max_stock || 100);
                return <Progress percent={level.percent} status={level.status} size="small" showInfo={false} />;
            },
        },
        {
            title: 'Reorder Qty',
            key: 'reorder',
            width: 100,
            render: (_, record) => {
                const needed = (record.max_stock || 100) - (record.stock_quantity || 0);
                return <span style={{ color: '#1890ff' }}>{formatNumber(Math.max(0, needed))}</span>;
            },
        },
        {
            title: 'Vendor',
            dataIndex: ['vendor', 'name'],
            key: 'vendor',
            width: 120,
            render: (v) => v || '-',
        },
    ];

    const rowSelection = {
        selectedRowKeys: selectedProducts,
        onChange: (keys) => setSelectedProducts(keys),
    };

    return (
        <MainLayout>
            <Head title="Low Stock Alert" />
            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/inventory')}>Back to Inventory</Button>
                    <Button type="primary" icon={<ShoppingCartOutlined />} onClick={createPurchaseOrder} disabled={selectedProducts.length === 0}>
                        Create Purchase Order ({selectedProducts.length})
                    </Button>
                </Space>
            </div>
            <Card
                title={
                    <Space>
                        <WarningOutlined style={{ color: '#faad14' }} />
                        <span>Low Stock Alert</span>
                    </Space>
                }
                extra={<Button icon={<ReloadOutlined />} onClick={() => handleFilter({})}>Refresh</Button>}
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search product..."
                        prefix={<SearchOutlined />}
                        allowClear
                        style={{ width: 200 }}
                        defaultValue={filters.search}
                        onChange={(e) => handleFilter({ search: e.target.value })}
                    />
                    {categories.length > 0 && (
                        <Select
                            placeholder="Category"
                            style={{ width: 150 }}
                            allowClear
                            defaultValue={filters.category_id}
                            onChange={(value) => handleFilter({ category_id: value })}
                            options={categories.map(c => ({ label: c.name, value: c.id }))}
                        />
                    )}
                    {stores.length > 0 && (
                        <Select
                            placeholder="Store"
                            style={{ width: 150 }}
                            allowClear
                            defaultValue={filters.store_id}
                            onChange={(value) => handleFilter({ store_id: value })}
                            options={stores.map(s => ({ label: s.name, value: s.id }))}
                        />
                    )}
                    <Select
                        placeholder="Stock Level"
                        style={{ width: 150 }}
                        allowClear
                        defaultValue={filters.level}
                        onChange={(value) => handleFilter({ level: value })}
                        options={[
                            { label: 'Out of Stock', value: 'out' },
                            { label: 'Critical', value: 'critical' },
                            { label: 'Low', value: 'low' },
                        ]}
                    />
                </Space>
                <Table
                    rowSelection={rowSelection}
                    dataSource={products.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    scroll={{ x: 1000 }}
                    pagination={{
                        total: products.meta?.total,
                        pageSize: products.meta?.per_page,
                        current: products.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                        showSizeChanger: false,
                    }}
                />
            </Card>
        </MainLayout>
    );
}
