import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, Space, Button, Tag } from 'antd';
import { SearchOutlined, PlusOutlined, EyeOutlined, EditOutlined, ReloadOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatDateTime, formatNumber } from '@/Helpers/formatters';

export default function CustomersIndex({ customers = { data: [], meta: {} }, filters = {} }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/customers', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const columns = [
        {
            title: 'Name',
            dataIndex: 'full_name',
            key: 'name',
        },
        {
            title: 'Email',
            dataIndex: 'email',
            key: 'email',
        },
        {
            title: 'Phone',
            dataIndex: 'phone',
            key: 'phone',
        },
        {
            title: 'Loyalty Points',
            dataIndex: 'loyalty_points',
            key: 'loyalty_points',
            render: (points) => formatNumber(points),
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            render: (status) => (
                <Tag color={status === 'active' ? 'green' : 'default'}>
                    {status === 'active' ? 'Active' : 'Inactive'}
                </Tag>
            ),
        },
        {
            title: 'Created',
            dataIndex: 'created_at',
            key: 'created_at',
            render: (date) => formatDateTime(date),
        },
        {
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Space>
                    <Button
                        icon={<EyeOutlined />}
                        size="small"
                        onClick={() => router.visit(`/customers/${record.id}`)}
                    >
                        View
                    </Button>
                    <Button
                        icon={<EditOutlined />}
                        size="small"
                        onClick={() => router.visit(`/customers/${record.id}/edit`)}
                    >
                        Edit
                    </Button>
                </Space>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Customers" />

            <Card
                title="Customers"
                extra={
                    <Space>
                        <Button
                            icon={<ReloadOutlined />}
                            onClick={() => handleFilter({})}
                        >
                            Refresh
                        </Button>
                        <Button
                            type="primary"
                            icon={<PlusOutlined />}
                            onClick={() => router.visit('/customers/create')}
                        >
                            Add Customer
                        </Button>
                    </Space>
                }
            >
                <Space style={{ marginBottom: 16 }} wrap>
                    <Input
                        placeholder="Search customers..."
                        prefix={<SearchOutlined />}
                        allowClear
                        style={{ width: 250 }}
                        defaultValue={filters.search}
                        onChange={(e) => handleFilter({ search: e.target.value })}
                    />
                    <Select
                        placeholder="Status"
                        style={{ width: 120 }}
                        allowClear
                        defaultValue={filters.status}
                        onChange={(value) => handleFilter({ status: value })}
                        options={[
                            { label: 'Active', value: 'active' },
                            { label: 'Inactive', value: 'inactive' },
                        ]}
                    />
                </Space>

                <Table
                    dataSource={customers.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    pagination={{
                        total: customers.meta?.total,
                        pageSize: customers.meta?.per_page,
                        current: customers.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                    }}
                />
            </Card>
        </MainLayout>
    );
}
