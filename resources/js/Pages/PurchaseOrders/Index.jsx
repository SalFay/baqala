import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, Space, Button, Tag } from 'antd';
import { SearchOutlined, PlusOutlined, EyeOutlined, ReloadOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';

export default function PurchaseOrdersIndex({ orders = { data: [], meta: {} }, filters = {} }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/purchase-orders', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const columns = [
        { title: 'PO #', dataIndex: 'po_number', key: 'po_number' },
        { title: 'Vendor', dataIndex: 'vendor', key: 'vendor' },
        { title: 'Store', dataIndex: 'store', key: 'store' },
        { title: 'Total', dataIndex: 'total', key: 'total', render: (total) => formatCurrency(total) },
        { title: 'Status', dataIndex: 'status', key: 'status', render: (status) => <StatusBadge status={status} /> },
        { title: 'Expected', dataIndex: 'expected_date', key: 'expected_date', render: (date) => date ? formatDateTime(date) : '-' },
        { title: 'Created', dataIndex: 'created_at', key: 'created_at', render: (date) => formatDateTime(date) },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Button icon={<EyeOutlined />} size="small" onClick={() => router.visit(`/purchase-orders/${record.id}`)}>View</Button>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Purchase Orders" />
            <Card
                title="Purchase Orders"
                extra={
                    <Space>
                        <Button icon={<ReloadOutlined />} onClick={() => handleFilter({})}>Refresh</Button>
                        <Button type="primary" icon={<PlusOutlined />} onClick={() => router.visit('/purchase-orders/create')}>Create PO</Button>
                    </Space>
                }
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search by PO number..."
                        prefix={<SearchOutlined />}
                        allowClear
                        style={{ width: 250 }}
                        defaultValue={filters.search}
                        onChange={(e) => handleFilter({ search: e.target.value })}
                    />
                    <Select
                        placeholder="Status"
                        style={{ width: 150 }}
                        allowClear
                        defaultValue={filters.status}
                        onChange={(value) => handleFilter({ status: value })}
                        options={[
                            { label: 'Draft', value: 'draft' },
                            { label: 'Ordered', value: 'ordered' },
                            { label: 'Partial', value: 'partial' },
                            { label: 'Received', value: 'received' },
                        ]}
                    />
                </Space>
                <Table
                    dataSource={orders.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    pagination={{
                        total: orders.meta?.total,
                        pageSize: orders.meta?.per_page,
                        current: orders.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                    }}
                />
            </Card>
        </MainLayout>
    );
}
