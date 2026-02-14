import { Button, List, InputNumber, Typography, Divider, Empty, Space } from 'antd';
import { DeleteOutlined, UserOutlined, MinusOutlined, PlusOutlined } from '@ant-design/icons';
import { formatCurrency } from '@/Helpers/formatters';

const { Text, Title } = Typography;

export default function Cart({
    cart,
    summary,
    onUpdateQuantity,
    onRemoveItem,
    onCheckout,
    onCustomerClick,
}) {
    const items = cart?.items || [];

    return (
        <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
            {/* Customer */}
            <div
                style={{
                    padding: '12px 16px',
                    borderBottom: '1px solid #f0f0f0',
                    cursor: 'pointer',
                    background: cart?.customer ? '#f6ffed' : '#fff',
                }}
                onClick={onCustomerClick}
            >
                <Space>
                    <UserOutlined />
                    <Text>{cart?.customer?.full_name || cart?.customer_name || 'Walk-in Customer'}</Text>
                    {cart?.customer?.loyalty_points > 0 && (
                        <Text type="secondary" style={{ fontSize: 12 }}>
                            ({cart.customer.loyalty_points} pts)
                        </Text>
                    )}
                </Space>
            </div>

            {/* Cart items */}
            <div style={{ flex: 1, overflow: 'auto', padding: 16 }}>
                {items.length === 0 ? (
                    <Empty description="Cart is empty" image={Empty.PRESENTED_IMAGE_SIMPLE} />
                ) : (
                    <List
                        dataSource={items}
                        renderItem={(item) => (
                            <List.Item
                                style={{ padding: '8px 0' }}
                                actions={[
                                    <Button
                                        type="text"
                                        danger
                                        icon={<DeleteOutlined />}
                                        onClick={() => onRemoveItem(item.id)}
                                        size="small"
                                    />,
                                ]}
                            >
                                <List.Item.Meta
                                    title={
                                        <Text ellipsis style={{ maxWidth: 150 }}>
                                            {item.product?.name || item.product_name}
                                            {item.variant_name && ` - ${item.variant_name}`}
                                        </Text>
                                    }
                                    description={
                                        <div>
                                            <Text type="secondary">
                                                {formatCurrency(item.price || item.unit_price)}
                                            </Text>
                                            <div style={{ marginTop: 4 }}>
                                                <Space size="small">
                                                    <Button
                                                        size="small"
                                                        icon={<MinusOutlined />}
                                                        onClick={() => onUpdateQuantity(item.id, item.quantity - 1)}
                                                        disabled={item.quantity <= 1}
                                                    />
                                                    <InputNumber
                                                        size="small"
                                                        min={1}
                                                        value={item.quantity}
                                                        onChange={(val) => val && onUpdateQuantity(item.id, val)}
                                                        style={{ width: 50, textAlign: 'center' }}
                                                        controls={false}
                                                    />
                                                    <Button
                                                        size="small"
                                                        icon={<PlusOutlined />}
                                                        onClick={() => onUpdateQuantity(item.id, item.quantity + 1)}
                                                    />
                                                </Space>
                                            </div>
                                        </div>
                                    }
                                />
                                <Text strong>{formatCurrency(item.subtotal || item.line_total)}</Text>
                            </List.Item>
                        )}
                    />
                )}
            </div>

            {/* Cart summary */}
            <div style={{ padding: 16, borderTop: '1px solid #f0f0f0', background: '#fafafa' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                    <Text>Subtotal</Text>
                    <Text>{formatCurrency(summary?.subtotal || 0)}</Text>
                </div>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                    <Text>Tax</Text>
                    <Text>{formatCurrency(summary?.tax_amount || 0)}</Text>
                </div>
                {(summary?.discount || 0) > 0 && (
                    <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
                        <Text type="success">Discount</Text>
                        <Text type="success">-{formatCurrency(summary?.discount || 0)}</Text>
                    </div>
                )}

                <Divider style={{ margin: '12px 0' }} />

                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 16 }}>
                    <Title level={4} style={{ margin: 0 }}>Total</Title>
                    <Title level={4} style={{ margin: 0, color: '#1890ff' }}>
                        {formatCurrency(summary?.total || 0)}
                    </Title>
                </div>

                <Button
                    type="primary"
                    size="large"
                    block
                    onClick={onCheckout}
                    disabled={items.length === 0}
                >
                    Checkout (F9)
                </Button>
            </div>
        </div>
    );
}
