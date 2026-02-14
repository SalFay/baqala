import { Modal, List, Typography, Button, Empty, Tag, Space } from 'antd';
import { ShoppingCartOutlined, ClockCircleOutlined, DeleteOutlined } from '@ant-design/icons';
import { formatCurrency, formatRelativeTime } from '@/Helpers/formatters';

const { Text, Title } = Typography;

export default function HoldOrdersModal({ open, onClose, heldOrders = [], onRestore, onDelete }) {
    // Ensure heldOrders is always an array
    const orders = Array.isArray(heldOrders) ? heldOrders : [];

    return (
        <Modal
            title={
                <Space>
                    <ShoppingCartOutlined />
                    <span>Held Orders</span>
                    <Tag>{orders.length}</Tag>
                </Space>
            }
            open={open}
            onCancel={onClose}
            footer={null}
            width={500}
        >
            {orders.length === 0 ? (
                <Empty
                    description="No held orders"
                    image={Empty.PRESENTED_IMAGE_SIMPLE}
                />
            ) : (
                <List
                    dataSource={orders}
                    renderItem={(order) => (
                        <List.Item
                            actions={[
                                <Button
                                    type="primary"
                                    size="small"
                                    onClick={() => onRestore(order.id)}
                                >
                                    Restore
                                </Button>,
                                onDelete && (
                                    <Button
                                        danger
                                        size="small"
                                        icon={<DeleteOutlined />}
                                        onClick={() => onDelete(order.id)}
                                    />
                                ),
                            ].filter(Boolean)}
                        >
                            <List.Item.Meta
                                avatar={
                                    <div
                                        style={{
                                            width: 40,
                                            height: 40,
                                            borderRadius: 8,
                                            background: '#1890ff',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            color: '#fff',
                                            fontWeight: 'bold',
                                        }}
                                    >
                                        {order.items_count || order.items?.length || 0}
                                    </div>
                                }
                                title={
                                    <Space>
                                        <Text strong>{order.name || order.hold_name || 'Unnamed Order'}</Text>
                                        {order.customer_name && (
                                            <Tag>{order.customer_name}</Tag>
                                        )}
                                    </Space>
                                }
                                description={
                                    <Space>
                                        <Text type="secondary">
                                            <ClockCircleOutlined style={{ marginRight: 4 }} />
                                            {formatRelativeTime(order.created_at || order.held_at)}
                                        </Text>
                                        <Text strong style={{ color: '#1890ff' }}>
                                            {formatCurrency(order.total || 0)}
                                        </Text>
                                    </Space>
                                }
                            />
                        </List.Item>
                    )}
                />
            )}
        </Modal>
    );
}
