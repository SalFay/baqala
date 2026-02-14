import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, Table, Input, Select, Space, Button, Tag, DatePicker } from 'antd';
import { ArrowLeftOutlined, SearchOutlined, ReloadOutlined, ArrowUpOutlined, ArrowDownOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatDateTime, formatNumber } from '@/Helpers/formatters';

const { RangePicker } = DatePicker;

export default function InventoryMovements({ movements = { data: [], meta: {} }, filters = {}, stores = [] }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/inventory/movements', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const getMovementTypeTag = (type) => {
        const types = {
            sale: { color: 'red', icon: <ArrowDownOutlined />, label: 'Sale' },
            purchase: { color: 'green', icon: <ArrowUpOutlined />, label: 'Purchase' },
            adjustment: { color: 'orange', label: 'Adjustment' },
            transfer_in: { color: 'blue', icon: <ArrowUpOutlined />, label: 'Transfer In' },
            transfer_out: { color: 'purple', icon: <ArrowDownOutlined />, label: 'Transfer Out' },
            return: { color: 'cyan', icon: <ArrowUpOutlined />, label: 'Return' },
        };
        const t = types[type] || { color: 'default', label: type };
        return <Tag color={t.color} icon={t.icon}>{t.label}</Tag>;
    };

    const columns = [
        { title: 'Date', dataIndex: 'created_at', key: 'date', render: (d) => formatDateTime(d), width: 160 },
        { title: 'Product', dataIndex: ['product', 'name'], key: 'product' },
        { title: 'SKU', dataIndex: ['product', 'sku'], key: 'sku', width: 120 },
        { title: 'Type', dataIndex: 'type', key: 'type', render: getMovementTypeTag, width: 130 },
        {
            title: 'Quantity',
            dataIndex: 'quantity',
            key: 'quantity',
            render: (qty, record) => (
                <span style={{ color: qty > 0 ? 'green' : 'red', fontWeight: 500 }}>
                    {qty > 0 ? '+' : ''}{formatNumber(qty)}
                </span>
            ),
            width: 100,
        },
        { title: 'Before', dataIndex: 'quantity_before', key: 'before', render: formatNumber, width: 80 },
        { title: 'After', dataIndex: 'quantity_after', key: 'after', render: formatNumber, width: 80 },
        { title: 'Reference', dataIndex: 'reference', key: 'reference', width: 150 },
        { title: 'Store', dataIndex: ['store', 'name'], key: 'store', width: 100 },
        { title: 'User', dataIndex: ['user', 'first_name'], key: 'user', width: 100 },
    ];

    return (
        <MainLayout>
            <Head title="Inventory Movements" />
            <div style={{ marginBottom: 24 }}>
                <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/inventory')}>Back to Inventory</Button>
            </div>
            <Card
                title="Inventory Movements"
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
                    <Select
                        placeholder="Type"
                        style={{ width: 150 }}
                        allowClear
                        defaultValue={filters.type}
                        onChange={(value) => handleFilter({ type: value })}
                        options={[
                            { label: 'Sale', value: 'sale' },
                            { label: 'Purchase', value: 'purchase' },
                            { label: 'Adjustment', value: 'adjustment' },
                            { label: 'Transfer In', value: 'transfer_in' },
                            { label: 'Transfer Out', value: 'transfer_out' },
                            { label: 'Return', value: 'return' },
                        ]}
                    />
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
                    <RangePicker
                        onChange={(dates) => {
                            if (dates) {
                                handleFilter({
                                    date_from: dates[0].format('YYYY-MM-DD'),
                                    date_to: dates[1].format('YYYY-MM-DD'),
                                });
                            } else {
                                handleFilter({ date_from: null, date_to: null });
                            }
                        }}
                    />
                </Space>
                <Table
                    dataSource={movements.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    scroll={{ x: 1200 }}
                    pagination={{
                        total: movements.meta?.total,
                        pageSize: movements.meta?.per_page,
                        current: movements.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                        showSizeChanger: false,
                    }}
                />
            </Card>
        </MainLayout>
    );
}
