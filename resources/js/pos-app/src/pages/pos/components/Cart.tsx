import { Button, List, InputNumber, Typography, Divider, Empty, Space } from 'antd';
import { DeleteOutlined, UserOutlined, MinusOutlined, PlusOutlined } from '@ant-design/icons';
import { useCartStore } from '../../../store/cartStore';

const { Text, Title } = Typography;

interface CartProps {
  onCheckout: () => void;
  onCustomerClick: () => void;
}

export default function Cart({ onCheckout, onCustomerClick }: CartProps) {
  const { cart, summary, updateItemQuantity, removeItem, isLoading } = useCartStore();

  const items = cart?.items || [];

  return (
    <div style={{ display: 'flex', flexDirection: 'column', height: '100%' }}>
      {/* Customer */}
      <div
        style={{
          padding: '12px 16px',
          borderBottom: '1px solid #f0f0f0',
          cursor: 'pointer',
        }}
        onClick={onCustomerClick}
      >
        <Space>
          <UserOutlined />
          <Text>{cart?.customer?.full_name || 'Walk-in Customer'}</Text>
          {cart?.customer && (
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
                className="cart-item"
                style={{ padding: '8px 0' }}
                actions={[
                  <Button
                    type="text"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => removeItem(item.id)}
                    size="small"
                  />,
                ]}
              >
                <List.Item.Meta
                  title={
                    <Text ellipsis style={{ maxWidth: 150 }}>
                      {item.product_name}
                      {item.variant_name && ` - ${item.variant_name}`}
                    </Text>
                  }
                  description={
                    <div>
                      <Text type="secondary">{item.unit_price.toFixed(2)} SAR</Text>
                      <div style={{ marginTop: 4 }}>
                        <Space size="small">
                          <Button
                            size="small"
                            icon={<MinusOutlined />}
                            onClick={() => updateItemQuantity(item.id, item.quantity - 1)}
                            disabled={item.quantity <= 1}
                          />
                          <InputNumber
                            size="small"
                            min={1}
                            value={item.quantity}
                            onChange={(val) => val && updateItemQuantity(item.id, val)}
                            style={{ width: 50, textAlign: 'center' }}
                            controls={false}
                          />
                          <Button
                            size="small"
                            icon={<PlusOutlined />}
                            onClick={() => updateItemQuantity(item.id, item.quantity + 1)}
                          />
                        </Space>
                      </div>
                    </div>
                  }
                />
                <Text strong>{item.line_total.toFixed(2)}</Text>
              </List.Item>
            )}
          />
        )}
      </div>

      {/* Cart summary */}
      <div style={{ padding: 16, borderTop: '1px solid #f0f0f0' }}>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text>Subtotal</Text>
          <Text>{(summary?.subtotal || 0).toFixed(2)} SAR</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text>Tax (VAT 15%)</Text>
          <Text>{(summary?.tax_amount || 0).toFixed(2)} SAR</Text>
        </div>
        {(summary?.discount ?? 0) > 0 && (
          <div
            style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}
          >
            <Text type="success">Discount</Text>
            <Text type="success">-{(summary?.discount ?? 0).toFixed(2)} SAR</Text>
          </div>
        )}
        {(summary?.loyalty_discount ?? 0) > 0 && (
          <div
            style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}
          >
            <Text type="success">Loyalty Discount</Text>
            <Text type="success">-{(summary?.loyalty_discount ?? 0).toFixed(2)} SAR</Text>
          </div>
        )}

        <Divider style={{ margin: '12px 0' }} />

        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            marginBottom: 16,
          }}
        >
          <Title level={4} style={{ margin: 0 }}>
            Total
          </Title>
          <Title level={4} style={{ margin: 0, color: '#1890ff' }}>
            {(summary?.total || 0).toFixed(2)} SAR
          </Title>
        </div>

        <Button
          type="primary"
          size="large"
          block
          onClick={onCheckout}
          disabled={items.length === 0}
          loading={isLoading}
        >
          Checkout (F9)
        </Button>
      </div>
    </div>
  );
}
