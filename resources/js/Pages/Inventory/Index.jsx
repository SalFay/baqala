import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, Space, Button, Tag, Modal, InputNumber, message } from 'antd';
import { SearchOutlined, ReloadOutlined, PlusOutlined, MinusOutlined, WarningOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import axios from 'axios';

export default function InventoryIndex({ inventory = { data: [], meta: {} }, filters = {} }) {
    const [loading, setLoading] = useState(false);
    const [adjustModal, setAdjustModal] = useState({ open: false, item: null });
    const [adjustData, setAdjustData] = useState({ quantity: 0, type: 'add', reason: '' });

    const handleFilter = (newFilters) => {
        router.get('/inventory', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const handleAdjust = async () => {
        try {
            await axios.post('/inventory/adjust', {
                product_id: adjustModal.item.product.id,
                store_id: 1,
                ...adjustData,
            });
            message.success('Inventory adjusted');
            setAdjustModal({ open: false, item: null });
            router.reload();
        } catch (error) {
            message.error('Failed to adjust inventory');
        }
    };

    const columns = [
        { title: 'Product', dataIndex: ['product', 'name'], key: 'product' },
        { title: 'SKU', dataIndex: ['product', 'sku'], key: 'sku' },
        { title: 'Category', dataIndex: ['product', 'category'], key: 'category' },
        {
            title: 'Quantity',
            dataIndex: 'quantity',
            key: 'quantity',
            render: (qty, record) => (
                <Tag color={record.is_low_stock ? 'red' : 'green'}>
                    {record.is_low_stock && <WarningOutlined />} {qty}
                </Tag>
            ),
        },
        { title: 'Min Qty', dataIndex: 'min_quantity', key: 'min_quantity' },
        { title: 'Max Qty', dataIndex: 'max_quantity', key: 'max_quantity' },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Space>
                    <Button icon={<PlusOutlined />} size="small" onClick={() => { setAdjustModal({ open: true, item: record }); setAdjustData({ quantity: 1, type: 'add', reason: '' }); }}>Add</Button>
                    <Button icon={<MinusOutlined />} size="small" onClick={() => { setAdjustModal({ open: true, item: record }); setAdjustData({ quantity: 1, type: 'subtract', reason: '' }); }}>Remove</Button>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Inventory" />
            <Card
                title="Inventory Management"
                extra={
                    <Space>
                        <Button type="dashed" onClick={() => router.visit('/inventory/low-stock')}>
                            <WarningOutlined /> Low Stock
                        </Button>
                        <Button onClick={() => router.visit('/inventory/movements')}>Movements</Button>
                        <Button icon={<ReloadOutlined />} onClick={() => handleFilter({})}>Refresh</Button>
                    </Space>
                }
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search products..."
                        prefix={<SearchOutlined />}
                        allowClear
                        style={{ width: 250 }}
                        defaultValue={filters.search}
                        onChange={(e) => handleFilter({ search: e.target.value })}
                    />
                    <Button type={filters.low_stock ? 'primary' : 'default'} onClick={() => handleFilter({ low_stock: !filters.low_stock })}>
                        Low Stock Only
                    </Button>
                </Space>
                <Table
                    dataSource={inventory.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    pagination={{
                        total: inventory.meta?.total,
                        pageSize: inventory.meta?.per_page,
                        current: inventory.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                    }}
                />
            </Card>

            <Modal
                title={`Adjust Inventory - ${adjustModal.item?.product?.name}`}
                open={adjustModal.open}
                onOk={handleAdjust}
                onCancel={() => setAdjustModal({ open: false, item: null })}
            >
                <Space direction="vertical" style={{ width: '100%' }}>
                    <Select
                        style={{ width: '100%' }}
                        value={adjustData.type}
                        onChange={(value) => setAdjustData({ ...adjustData, type: value })}
                        options={[
                            { label: 'Add Stock', value: 'add' },
                            { label: 'Remove Stock', value: 'subtract' },
                            { label: 'Set Stock', value: 'set' },
                        ]}
                    />
                    <InputNumber
                        style={{ width: '100%' }}
                        min={1}
                        value={adjustData.quantity}
                        onChange={(value) => setAdjustData({ ...adjustData, quantity: value })}
                        placeholder="Quantity"
                    />
                    <Input.TextArea
                        rows={2}
                        value={adjustData.reason}
                        onChange={(e) => setAdjustData({ ...adjustData, reason: e.target.value })}
                        placeholder="Reason for adjustment"
                    />
                </Space>
            </Modal>
        </MainLayout>
    );
}
