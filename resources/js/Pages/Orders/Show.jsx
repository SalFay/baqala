import { useState, useEffect } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    Card, Row, Col, Descriptions, Table, Button, Space, Tabs, message,
} from 'antd';
import { ArrowLeftOutlined, PrinterOutlined, EditOutlined } from '@ant-design/icons';
import MainLayout from '@/Components/Layout/MainLayout';
import StatusBadge from '@/Components/StatusBadge';
import StatusHistory from '@/Components/StatusHistory';
import StatusChangeModal from '@/Components/StatusChangeModal';
import ActivityLog from '@/Components/ActivityLog';
import { formatCurrency, formatDateTime } from '@/Helpers/formatters';
import orderService from '@/Helpers/api/orderService';

export default function OrderShow({ order }) {
    const [statusModalOpen, setStatusModalOpen] = useState(false);
    const [availableStatuses, setAvailableStatuses] = useState([]);
    const [statusHistories, setStatusHistories] = useState([]);
    const [activities, setActivities] = useState([]);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        loadStatusData();
    }, [order.id]);

    const loadStatusData = async () => {
        try {
            setLoading(true);
            const [statusesRes, historyRes, activityRes] = await Promise.all([
                orderService.getAvailableStatuses(order.id),
                orderService.getStatusHistory(order.id),
                orderService.getActivityLog(order.id),
            ]);

            setAvailableStatuses(statusesRes.data.available_statuses || []);
            setStatusHistories(historyRes.data.data || []);
            setActivities(activityRes.data.data || []);
        } catch (error) {
            console.error('Failed to load status data:', error);
        } finally {
            setLoading(false);
        }
    };

    const handleStatusChange = async (status, reason) => {
        await orderService.updateStatus(order.id, status, reason);
        router.reload();
        loadStatusData();
    };

    const itemColumns = [
        { title: 'Product', dataIndex: 'product_name', key: 'product_name' },
        { title: 'SKU', dataIndex: 'sku', key: 'sku' },
        { title: 'Qty', dataIndex: 'quantity', key: 'quantity' },
        {
            title: 'Unit Price',
            dataIndex: 'unit_price',
            key: 'unit_price',
            render: (price) => formatCurrency(price),
        },
        {
            title: 'Total',
            dataIndex: 'line_total',
            key: 'line_total',
            render: (total) => formatCurrency(total),
        },
    ];

    const tabItems = [
        {
            key: 'details',
            label: 'Details',
            children: (
                <Row gutter={[24, 24]}>
                    <Col xs={24} lg={16}>
                        <Card title="Order Items" style={{ marginBottom: 16 }}>
                            <Table
                                dataSource={order.items}
                                columns={itemColumns}
                                rowKey="id"
                                pagination={false}
                                summary={() => (
                                    <Table.Summary>
                                        <Table.Summary.Row>
                                            <Table.Summary.Cell colSpan={4} align="right">
                                                <strong>Subtotal:</strong>
                                            </Table.Summary.Cell>
                                            <Table.Summary.Cell>
                                                {formatCurrency(order.subtotal)}
                                            </Table.Summary.Cell>
                                        </Table.Summary.Row>
                                        {order.discount > 0 && (
                                            <Table.Summary.Row>
                                                <Table.Summary.Cell colSpan={4} align="right">
                                                    <strong>Discount:</strong>
                                                </Table.Summary.Cell>
                                                <Table.Summary.Cell>
                                                    -{formatCurrency(order.discount)}
                                                </Table.Summary.Cell>
                                            </Table.Summary.Row>
                                        )}
                                        <Table.Summary.Row>
                                            <Table.Summary.Cell colSpan={4} align="right">
                                                <strong>Tax:</strong>
                                            </Table.Summary.Cell>
                                            <Table.Summary.Cell>
                                                {formatCurrency(order.tax_amount)}
                                            </Table.Summary.Cell>
                                        </Table.Summary.Row>
                                        <Table.Summary.Row>
                                            <Table.Summary.Cell colSpan={4} align="right">
                                                <strong style={{ fontSize: 16 }}>Total:</strong>
                                            </Table.Summary.Cell>
                                            <Table.Summary.Cell>
                                                <strong style={{ fontSize: 16 }}>
                                                    {formatCurrency(order.total)}
                                                </strong>
                                            </Table.Summary.Cell>
                                        </Table.Summary.Row>
                                    </Table.Summary>
                                )}
                            />
                        </Card>
                    </Col>
                    <Col xs={24} lg={8}>
                        <Card title="Order Info">
                            <Descriptions column={1} size="small">
                                <Descriptions.Item label="Order #">
                                    {order.order_number}
                                </Descriptions.Item>
                                <Descriptions.Item label="Invoice #">
                                    {order.invoice_no}
                                </Descriptions.Item>
                                <Descriptions.Item label="Status">
                                    <StatusBadge status={order.current_status} />
                                </Descriptions.Item>
                                <Descriptions.Item label="Payment Status">
                                    {order.payment_status}
                                </Descriptions.Item>
                                <Descriptions.Item label="Customer">
                                    {order.customer?.full_name || order.customer_name || 'Walk-in'}
                                </Descriptions.Item>
                                <Descriptions.Item label="Cashier">
                                    {order.user?.first_name || order.cashier_name}
                                </Descriptions.Item>
                                <Descriptions.Item label="Date">
                                    {formatDateTime(order.created_at)}
                                </Descriptions.Item>
                            </Descriptions>
                        </Card>
                    </Col>
                </Row>
            ),
        },
        {
            key: 'status-history',
            label: 'Status History',
            children: <StatusHistory histories={statusHistories} loading={loading} />,
        },
        {
            key: 'activity-log',
            label: 'Activity Log',
            children: <ActivityLog activities={activities} loading={loading} />,
        },
    ];

    return (
        <MainLayout>
            <Head title={`Order ${order.order_number}`} />

            <div style={{ marginBottom: 24 }}>
                <Space>
                    <Button
                        icon={<ArrowLeftOutlined />}
                        onClick={() => router.visit('/orders')}
                    >
                        Back to Orders
                    </Button>
                    <Button icon={<PrinterOutlined />}>Print Receipt</Button>
                    <Button
                        icon={<EditOutlined />}
                        onClick={() => setStatusModalOpen(true)}
                    >
                        Change Status
                    </Button>
                </Space>
            </div>

            <Tabs items={tabItems} />

            <StatusChangeModal
                open={statusModalOpen}
                onClose={() => setStatusModalOpen(false)}
                currentStatus={order.current_status}
                availableStatuses={availableStatuses}
                onStatusChange={handleStatusChange}
                loading={loading}
            />
        </MainLayout>
    );
}
