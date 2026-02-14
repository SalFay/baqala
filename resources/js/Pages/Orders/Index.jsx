import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, DatePicker, Space, Button, Tag } from 'antd';
import { SearchOutlined, EyeOutlined, ReloadOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';

const { RangePicker } = DatePicker;

export default function OrdersIndex({ orders = { data: [], meta: {} }, filters = {} }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/orders', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const columns = [
        {
            title: 'Order #',
            dataIndex: 'order_number',
            key: 'order_number',
        },
        {
            title: 'Customer',
            dataIndex: 'customer_name',
            key: 'customer_name',
            render: (name, record) => name || record.customer?.full_name || 'Walk-in',
        },
        {
            title: 'Total',
            dataIndex: 'total',
            key: 'total',
            render: (total) => formatCurrency(total),
        },
        {
            title: 'Status',
            dataIndex: 'current_status',
            key: 'status',
            render: (status) => <StatusBadge status={status} />,
        },
        {
            title: 'Payment',
            dataIndex: 'payment_status',
            key: 'payment_status',
            render: (status) => {
                const colors = {
                    paid: 'green',
                    pending: 'orange',
                    partial: 'blue',
                    refunded: 'red',
                };
                return <Tag color={colors[status]}>{status}</Tag>;
            },
        },
        {
            title: 'Date',
            dataIndex: 'created_at',
            key: 'created_at',
            render: (date) => formatDateTime(date),
        },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Button
                    icon={<EyeOutlined />}
                    size="small"
                    onClick={() => router.visit(`/orders/${record.id}`)}
                >
                    View
                </Button>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Orders" />

            <Card
                title="Orders"
                extra={
                    <Button
                        icon={<ReloadOutlined />}
                        onClick={() => handleFilter({})}
                    >
                        Refresh
                    </Button>
                }
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search orders..."
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
                            { label: 'Processing', value: 'processing' },
                            { label: 'Completed', value: 'completed' },
                            { label: 'Cancelled', value: 'cancelled' },
                        ]}
                    />
                    <RangePicker
                        onChange={(dates) => {
                            if (dates) {
                                handleFilter({
                                    from_date: dates[0].format('YYYY-MM-DD'),
                                    to_date: dates[1].format('YYYY-MM-DD'),
                                });
                            } else {
                                handleFilter({ from_date: null, to_date: null });
                            }
                        }}
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
