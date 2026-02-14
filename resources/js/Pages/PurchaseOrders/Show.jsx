import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Descriptions, Table, Button, Space, Tag, message } from 'antd';
import { ArrowLeftOutlined, CheckOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';

export default function PurchaseOrderShow({ order }) {
    const handleReceive = () => {
        router.post(`/purchase-orders/${order.id}/receive`, {
            items: order.items.map(item => ({
                id: item.id,
                received_quantity: item.quantity,
            })),
        }, {
            onSuccess: () => message.success('Items received'),
        });
    };

    const columns = [
        { title: 'Product', dataIndex: 'product', key: 'product' },
        { title: 'Ordered', dataIndex: 'quantity', key: 'quantity' },
        { title: 'Received', dataIndex: 'received_quantity', key: 'received' },
        { title: 'Unit Cost', dataIndex: 'unit_cost', key: 'unit_cost', render: (val) => formatCurrency(val) },
        { title: 'Total', dataIndex: 'total', key: 'total', render: (val) => formatCurrency(val) },
    ];

    return (
        <MainLayout>
            <Head title={`PO ${order.po_number}`} />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/purchase-orders')}>Back</Button>
                    {order.status === 'ordered' && (
                        <Button type="primary" icon={<CheckOutlined />} onClick={handleReceive}>Receive All</Button>
                    )}
                </Space>
            </div>

            <Row gutter={[24, 24]}>
                <Col xs={24} lg={8}>
                    <Card title="Order Info">
                        <Descriptions column={1} size="small">
                            <Descriptions.Item label="PO Number">{order.po_number}</Descriptions.Item>
                            <Descriptions.Item label="Status"><StatusBadge status={order.status} /></Descriptions.Item>
                            <Descriptions.Item label="Vendor">{order.vendor?.name}</Descriptions.Item>
                            <Descriptions.Item label="Store">{order.store?.name}</Descriptions.Item>
                            <Descriptions.Item label="Expected">{order.expected_date ? formatDateTime(order.expected_date) : '-'}</Descriptions.Item>
                            <Descriptions.Item label="Created">{formatDateTime(order.created_at)}</Descriptions.Item>
                        </Descriptions>
                    </Card>
                </Col>
                <Col xs={24} lg={16}>
                    <Card title="Items">
                        <Table
                            dataSource={order.items}
                            columns={columns}
                            rowKey="id"
                            pagination={false}
                            summary={() => (
                                <Table.Summary>
                                    <Table.Summary.Row>
                                        <Table.Summary.Cell colSpan={4} align="right"><strong>Subtotal:</strong></Table.Summary.Cell>
                                        <Table.Summary.Cell>{formatCurrency(order.subtotal)}</Table.Summary.Cell>
                                    </Table.Summary.Row>
                                    <Table.Summary.Row>
                                        <Table.Summary.Cell colSpan={4} align="right"><strong>Tax:</strong></Table.Summary.Cell>
                                        <Table.Summary.Cell>{formatCurrency(order.tax_amount)}</Table.Summary.Cell>
                                    </Table.Summary.Row>
                                    <Table.Summary.Row>
                                        <Table.Summary.Cell colSpan={4} align="right"><strong>Total:</strong></Table.Summary.Cell>
                                        <Table.Summary.Cell><strong>{formatCurrency(order.total)}</strong></Table.Summary.Cell>
                                    </Table.Summary.Row>
                                </Table.Summary>
                            )}
                        />
                    </Card>
                </Col>
            </Row>

            {order.notes && (
                <Card title="Notes" style={{ marginTop: 24 }}>
                    <p>{order.notes}</p>
                </Card>
            )}
        </MainLayout>
    );
}
