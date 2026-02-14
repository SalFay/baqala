import { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Table, Card, Input, Select, Space, Button, Tag, Image } from 'antd';
import { SearchOutlined, PlusOutlined, EditOutlined, ReloadOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency } from '@/Helpers/formatters';

export default function ProductsIndex({ products = { data: [], meta: {} }, categories = [], filters = {} }) {
    const [loading, setLoading] = useState(false);

    const handleFilter = (newFilters) => {
        router.get('/products', { ...filters, ...newFilters }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const columns = [
        {
            title: 'Image',
            dataIndex: 'image_url',
            key: 'image',
            width: 80,
            render: (url) => (
                <Image
                    src={url || '/images/no-image.png'}
                    alt="Product"
                    width={50}
                    height={50}
                    style={{ objectFit: 'cover', borderRadius: 4 }}
                    fallback="/images/no-image.png"
                />
            ),
        },
        {
            title: 'Name',
            dataIndex: 'name',
            key: 'name',
        },
        {
            title: 'SKU',
            dataIndex: 'sku',
            key: 'sku',
        },
        {
            title: 'Category',
            dataIndex: 'category',
            key: 'category',
        },
        {
            title: 'Price',
            dataIndex: 'price',
            key: 'price',
            render: (price) => formatCurrency(price),
        },
        {
            title: 'Stock',
            dataIndex: 'stock',
            key: 'stock',
            render: (stock) => (
                <Tag color={stock <= 10 ? 'red' : stock <= 20 ? 'orange' : 'green'}>
                    {stock}
                </Tag>
            ),
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
            title: 'Actions',
            key: 'actions',
            render: (_, record) => (
                <Button
                    icon={<EditOutlined />}
                    size="small"
                    onClick={() => router.visit(`/products/${record.id}/edit`)}
                >
                    Edit
                </Button>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title="Products" />

            <Card
                title="Products"
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
                            onClick={() => router.visit('/products/create')}
                        >
                            Add Product
                        </Button>
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
                    <Select
                        placeholder="Category"
                        style={{ width: 180 }}
                        allowClear
                        defaultValue={filters.category_id}
                        onChange={(value) => handleFilter({ category_id: value })}
                        options={categories.map(c => ({ label: c.name, value: c.id }))}
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
                    dataSource={products.data}
                    columns={columns}
                    rowKey="id"
                    loading={loading}
                    pagination={{
                        total: products.meta?.total,
                        pageSize: products.meta?.per_page,
                        current: products.meta?.current_page,
                        onChange: (page) => handleFilter({ page }),
                    }}
                />
            </Card>
        </MainLayout>
    );
}
