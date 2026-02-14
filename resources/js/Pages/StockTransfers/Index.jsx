import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, Space, Button } from 'antd';
import { SearchOutlined, PlusOutlined, EyeOutlined, ReloadOutlined, SwapOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatDateTime } from '@/Helpers/formatters';

export default function StockTransfersIndex({ transfers = { data: [], meta: {} }, filters = {} }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/stock-transfers', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const columns = [
        { title: 'Transfer #', dataIndex: 'transfer_number', key: 'transfer_number' },
        {
            title: 'Route',
            key: 'route',
            render: (_, record) => (
                <span>{record.from_store} <SwapOutlined /> {record.to_store}</span>
            ),
        },
        { title: 'Items', dataIndex: 'items_count', key: 'items_count' },
        { title: 'Status', dataIndex: 'status', key: 'status', render: (status) => <StatusBadge status={status} /> },
        { title: 'Created', dataIndex: 'created_at', key: 'created_at', render: (date) => formatDateTime(date) },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Button icon={<EyeOutlined />} size="small" onClick={() => router.visit(`/stock-transfers/${record.id}`)}>View</Button>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Stock Transfers" />
            <Card
                title="Stock Transfers"
                extra={
                    <Space>
                        <Button icon={<ReloadOutlined />} onClick={() => handleFilter({})}>Refresh</Button>
                        <Button type="primary" icon={<PlusOutlined />} onClick={() => router.visit('/stock-transfers/create')}>New Transfer</Button>
                    </Space>
                }
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search..."
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
                            { label: 'Pending', value: 'pending' },
                            { label: 'In Transit', value: 'in_transit' },
                            { label: 'Completed', value: 'completed' },
                        ]}
                    />
                </Space>
                <Table
                    dataSource={transfers.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    pagination={{
                        total: transfers.meta?.total,
                        pageSize: transfers.meta?.per_page,
                        current: transfers.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                    }}
                />
            </Card>
        </MainLayout>
    );
}
