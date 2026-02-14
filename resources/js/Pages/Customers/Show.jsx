import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Descriptions, Table, Button, Space, Tag } from 'antd';
import { ArrowLeftOutlined, EditOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatCurrency, formatDateTime, formatNumber } from '@/Helpers/formatters';

export default function CustomerShow({ customer }) {
    const orderColumns = [
        { title: 'Order #', dataIndex: 'order_number', key: 'order_number' },
        {
            title: 'Total',
            dataIndex: 'total',
            key: 'total',
            render: (total) => formatCurrency(total),
        },
        {
            title: 'Status',
            dataIndex: 'status',
            key: 'status',
            render: (status) => <StatusBadge status={status} />,
        },
        {
            title: 'Date',
            dataIndex: 'created_at',
            key: 'created_at',
            render: (date) => formatDateTime(date),
        },
    ];

    return (
        <MainLayout>
            <Head title={`Customer: ${customer.full_name}`} />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button
                        icon={<ArrowLeftOutlined />}
                        onClick={() => router.visit('/customers')}
                    >
                        Back to Customers
                    </Button>
                    <Button
                        icon={<EditOutlined />}
                        onClick={() => router.visit(`/customers/${customer.id}/edit`)}
                    >
                        Edit
                    </Button>
                </Space>
            </div>

            <Row gutter={[24, 24]}>
                <Col xs={24} lg={8}>
                    <Card title="Customer Information">
                        <Descriptions column={1} size="small">
                            <Descriptions.Item label="Name">
                                {customer.full_name}
                            </Descriptions.Item>
                            <Descriptions.Item label="Email">
                                {customer.email || '-'}
                            </Descriptions.Item>
                            <Descriptions.Item label="Phone">
                                {customer.phone || '-'}
                            </Descriptions.Item>
                            <Descriptions.Item label="Address">
                                {customer.address || '-'}
                            </Descriptions.Item>
                            <Descriptions.Item label="City">
                                {customer.city || '-'}
                            </Descriptions.Item>
                            <Descriptions.Item label="Status">
                                <Tag color={customer.status === 'active' ? 'green' : 'default'}>
                                    {customer.status === 'active' ? 'Active' : 'Inactive'}
                                </Tag>
                            </Descriptions.Item>
                            <Descriptions.Item label="Created">
                                {formatDateTime(customer.created_at)}
                            </Descriptions.Item>
                        </Descriptions>
                    </Card>

                    <Card title="Loyalty & Credit" style={{ marginTop: 16 }}>
                        <Descriptions column={1} size="small">
                            <Descriptions.Item label="Loyalty Points">
                                {formatNumber(customer.loyalty_points)}
                            </Descriptions.Item>
                            <Descriptions.Item label="Credit Limit">
                                {formatCurrency(customer.credit_limit)}
                            </Descriptions.Item>
                            <Descriptions.Item label="Credit Balance">
                                {formatCurrency(customer.credit_balance)}
                            </Descriptions.Item>
                        </Descriptions>
                    </Card>
                </Col>

                <Col xs={24} lg={16}>
                    <Card title="Recent Orders">
                        <Table
                            dataSource={customer.recent_orders}
                            columns={orderColumns}
                            rowKey="id"
                            pagination={false}
                        />
                    </Card>
                </Col>
            </Row>
        </MainLayout>
    );
}
