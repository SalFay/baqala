import { Head, router } from '@inertiajs/react';
import { Card, Row, Col, Descriptions, Table, Button, Space, message } from 'antd';
import { ArrowLeftOutlined, SendOutlined, CheckOutlined, SwapOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import { formatDateTime } from '@/Helpers/formatters';

export default function StockTransferShow({ transfer }) {
    const handleShip = () => {
        router.post(`/stock-transfers/${transfer.id}/ship`, {
            items: transfer.items.map(item => ({
                id: item.id,
                shipped_quantity: item.quantity,
            })),
        }, {
            onSuccess: () => message.success('Transfer shipped'),
        });
    };

    const handleReceive = () => {
        router.post(`/stock-transfers/${transfer.id}/receive`, {
            items: transfer.items.map(item => ({
                id: item.id,
                received_quantity: item.shipped_quantity || item.quantity,
            })),
        }, {
            onSuccess: () => message.success('Transfer received'),
        });
    };

    const columns = [
        { title: 'Product', dataIndex: 'product', key: 'product' },
        { title: 'Requested', dataIndex: 'quantity', key: 'quantity' },
        { title: 'Shipped', dataIndex: 'shipped_quantity', key: 'shipped', render: (v) => v || '-' },
        { title: 'Received', dataIndex: 'received_quantity', key: 'received', render: (v) => v || '-' },
    ];

    return (
        <MainLayout>
            <Head title={`Transfer ${transfer.transfer_number}`} />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button icon={<ArrowLeftOutlined />} onClick={() => router.visit('/stock-transfers')}>Back</Button>
                    {transfer.status === 'pending' && (
                        <Button type="primary" icon={<SendOutlined />} onClick={handleShip}>Ship Items</Button>
                    )}
                    {transfer.status === 'in_transit' && (
                        <Button type="primary" icon={<CheckOutlined />} onClick={handleReceive}>Receive Items</Button>
                    )}
                </Space>
            </div>

            <Row gutter={[24, 24]}>
                <Col xs={24} lg={8}>
                    <Card title="Transfer Info">
                        <Descriptions column={1} size="small">
                            <Descriptions.Item label="Transfer #">{transfer.transfer_number}</Descriptions.Item>
                            <Descriptions.Item label="Status"><StatusBadge status={transfer.status} /></Descriptions.Item>
                            <Descriptions.Item label="Route">
                                {transfer.from_store?.name} <SwapOutlined /> {transfer.to_store?.name}
                            </Descriptions.Item>
                            <Descriptions.Item label="Created">{formatDateTime(transfer.created_at)}</Descriptions.Item>
                        </Descriptions>
                    </Card>
                </Col>
                <Col xs={24} lg={16}>
                    <Card title="Items">
                        <Table
                            dataSource={transfer.items}
                            columns={columns}
                            rowKey="id"
                            pagination={false}
                        />
                    </Card>
                </Col>
            </Row>

            {transfer.notes && (
                <Card title="Notes" style={{ marginTop: 24 }}>
                    <p>{transfer.notes}</p>
                </Card>
            )}
        </MainLayout>
    );
}
