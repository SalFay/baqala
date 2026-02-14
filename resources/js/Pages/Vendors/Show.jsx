import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Descriptions, Table, Button, Space, Tag, Statistic } from 'antd';
import { ArrowLeftOutlined, EditOutlined, ShoppingCartOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';

export default function VendorShow({ vendor, purchaseOrders = [], stats = {} }) {
    const columns = [
        { title: 'PO Number', dataIndex: 'po_number', key: 'po_number' },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            render: (status) => {
                const colors = { draft: 'default', ordered: 'blue', partial: 'orange', received: 'green', cancelled: 'red' };
                return <Tag color={colors[status] || 'default'}>{status}</Tag>;
            },
        },
        { title: 'Total', dataIndex: 'total', key: 'total', render: (val) => formatCurrency(val) },
        { title: 'Date', dataIndex: 'created_at', key: 'date', render: (date) => formatDateTime(date) },
        {
            title: '',
            key: 'actions',
            render: (_, record) => (
                <Button size="small" onClick={() => router.visit(`/purchase-orders/${record.id}`)}>View</Button>
            ),
        },
    ];

    return (
        <MainLayout>
            <Head title={`Vendor - ${vendor.name}`} />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/vendors')}>Back</Button>
                    <Button type="primary" icon={<EditOutlined />} onClick={() => router.visit(`/vendors/${vendor.id}/edit`)}>Edit</Button>
                    <Button icon={<ShoppingCartOutlined />} onClick={() => router.visit('/purchase-orders/create')}>New Purchase Order</Button>
                </Space>
            </div>

            <Row gutter={[24, 24]}>
                <Col xs={24} lg={8}>
                    <Card title="Vendor Information">
                        <Descriptions column={1} size="small">
                            <Descriptions.Item label="Name">{vendor.name}</Descriptions.Item>
                            <Descriptions.Item label="Email">{vendor.email || '-'}</Descriptions.Item>
                            <Descriptions.Item label="Phone">{vendor.phone || '-'}</Descriptions.Item>
                            <Descriptions.Item label="Address">{vendor.address || '-'}</Descriptions.Item>
                            <Descriptions.Item label="City">{vendor.city || '-'}</Descriptions.Item>
                            <Descriptions.Item label="Country">{vendor.country || '-'}</Descriptions.Item>
                            <Descriptions.Item label="Tax Number">{vendor.tax_number || '-'}</Descriptions.Item>
                            <Descriptions.Item label="Status">
                                <Tag color={vendor.status === 'active' ? 'green' : 'default'}>
                                    {vendor.status === 'active' ? 'Active' : 'Inactive'}
                                </Tag>
                            </Descriptions.Item>
                            <Descriptions.Item label="Created">{formatDateTime(vendor.created_at)}</Descriptions.Item>
                        </Descriptions>
                    </Card>
                </Col>
                <Col xs={24} lg={16}>
                    <Row gutter={[16, 16]} style={{ marginBottom: 24 }}>
                        <Col xs={12} sm={8}>
                            <Card>
                                <Statistic title="Total Orders" value={stats.total_orders || 0} />
                            </Card>
                        </Col>
                        <Col xs={12} sm={8}>
                            <Card>
                                <Statistic title="Total Spent" value={stats.total_spent || 0} prefix="$" precision={2} />
                            </Card>
                        </Col>
                        <Col xs={12} sm={8}>
                            <Card>
                                <Statistic title="Pending Orders" value={stats.pending_orders || 0} />
                            </Card>
                        </Col>
                    </Row>

                    <Card title="Recent Purchase Orders">
                        <Table
                            dataSource={purchaseOrders}
                            columns={columns}
                            rowKey="id"
                            pagination={false}
                            locale={{ emptyText: 'No purchase orders yet' }}
                        />
                    </Card>
                </Col>
            </Row>
        </MainLayout>
    );
}
