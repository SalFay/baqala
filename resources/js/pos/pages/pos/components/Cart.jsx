import { Button, List, InputNumber, Typography, Divider, Empty, Space, Badge, Card } from 'antd';
import {
  DeleteOutlined,
  UserOutlined,
  MinusOutlined,
  PlusOutlined,
  ShoppingCartOutlined,
  CreditCardOutlined,
} from '@ant-design/icons';
import { useCartStore } from '@pos/store/cartStore';

const { Text, Title } = Typography;

export default function Cart({ onCheckout, onCustomerClick }) {
  const { cart, summary, updateItemQuantity, removeItem, isLoading } = useCartStore();

  const items = cart?.items || [];
  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);

  return (
    <div
      style={{
        display: 'flex',
        flexDirection: 'column',
        height: '100%',
        background: '#fafafa',
      }}
    >
      {/* Customer Selection */}
      <div
        style={{
          padding: '16px',
          background: '#fff',
          borderBottom: '1px solid #f0f0f0',
          cursor: 'pointer',
          transition: 'background 0.2s',
        }}
        onClick={onCustomerClick}
        onMouseEnter={(e) => (e.currentTarget.style.background = '#f5f5f5')}
        onMouseLeave={(e) => (e.currentTarget.style.background = '#fff')}
      >
        <Space align="center">
          <div
            style={{
              width: 36,
              height: 36,
              borderRadius: '50%',
              background: cart?.customer ? '#1890ff' : '#e8e8e8',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}
          >
            <UserOutlined style={{ color: cart?.customer ? '#fff' : '#999', fontSize: 16 }} />
          </div>
          <div>
            <Text strong style={{ display: 'block' }}>
              {cart?.customer?.full_name || 'Walk-in Customer'}
            </Text>
            {cart?.customer && (
              <Text type="secondary" style={{ fontSize: 12 }}>
                {cart.customer.loyalty_points || 0} loyalty points
              </Text>
            )}
          </div>
        </Space>
      </div>

      {/* Cart Items */}
      <div style={{ flex: 1, overflow: 'auto', padding: '8px 16px' }}>
        {items.length === 0 ? (
          <div
            style={{
              display: 'flex',
              flexDirection: 'column',
              alignItems: 'center',
              justifyContent: 'center',
              height: '100%',
              padding: 40,
            }}
          >
            <ShoppingCartOutlined style={{ fontSize: 48, color: '#d9d9d9', marginBottom: 16 }} />
            <Text type="secondary">Cart is empty</Text>
            <Text type="secondary" style={{ fontSize: 12 }}>
              Click products to add them
            </Text>
          </div>
        ) : (
          <List
            dataSource={items}
            renderItem={(item) => (
              <Card
                size="small"
                style={{
                  marginBottom: 8,
                  borderRadius: 8,
                  boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
                }}
                bodyStyle={{ padding: '12px' }}
              >
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                  <div style={{ flex: 1, marginRight: 8 }}>
                    <Text strong style={{ display: 'block', marginBottom: 4 }}>
                      {item.product_name}
                      {item.variant_name && (
                        <Text type="secondary" style={{ fontWeight: 'normal' }}>
                          {' '}
                          - {item.variant_name}
                        </Text>
                      )}
                    </Text>
                    <Text type="secondary" style={{ fontSize: 13 }}>
                      {Number(item.unit_price).toFixed(2)} SAR
                    </Text>
                  </div>
                  <Text strong style={{ fontSize: 15, color: '#1890ff' }}>
                    {Number(item.line_total).toFixed(2)}
                  </Text>
                </div>

                <div
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                    marginTop: 8,
                  }}
                >
                  <Space size={4}>
                    <Button
                      size="small"
                      type={item.quantity <= 1 ? 'default' : 'primary'}
                      ghost={item.quantity > 1}
                      icon={<MinusOutlined />}
                      onClick={() => updateItemQuantity(item.id, item.quantity - 1)}
                      disabled={item.quantity <= 1}
                      style={{ borderRadius: 6 }}
                    />
                    <InputNumber
                      size="small"
                      min={1}
                      value={item.quantity}
                      onChange={(val) => val && updateItemQuantity(item.id, val)}
                      style={{
                        width: 50,
                        textAlign: 'center',
                        borderRadius: 6,
                      }}
                      controls={false}
                    />
                    <Button
                      size="small"
                      type="primary"
                      ghost
                      icon={<PlusOutlined />}
                      onClick={() => updateItemQuantity(item.id, item.quantity + 1)}
                      style={{ borderRadius: 6 }}
                    />
                  </Space>
                  <Button
                    type="text"
                    danger
                    icon={<DeleteOutlined />}
                    onClick={() => removeItem(item.id)}
                    size="small"
                  />
                </div>
              </Card>
            )}
          />
        )}
      </div>

      {/* Cart Summary */}
      <div
        style={{
          padding: 16,
          background: '#fff',
          borderTop: '1px solid #f0f0f0',
          boxShadow: '0 -2px 8px rgba(0,0,0,0.05)',
        }}
      >
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Subtotal</Text>
          <Text>{Number(summary?.subtotal || 0).toFixed(2)} SAR</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Tax (VAT 15%)</Text>
          <Text>{Number(summary?.tax_amount || 0).toFixed(2)} SAR</Text>
        </div>
        {Number(summary?.discount) > 0 && (
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
            <Text style={{ color: '#52c41a' }}>Discount</Text>
            <Text style={{ color: '#52c41a' }}>-{Number(summary.discount).toFixed(2)} SAR</Text>
          </div>
        )}

        <Divider style={{ margin: '12px 0' }} />

        <div
          style={{
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
            marginBottom: 16,
          }}
        >
          <Title level={4} style={{ margin: 0 }}>
            Total
          </Title>
          <Title level={4} style={{ margin: 0, color: '#1890ff' }}>
            {Number(summary?.total || 0).toFixed(2)} SAR
          </Title>
        </div>

        <Badge count={itemCount} offset={[-10, 0]} style={{ backgroundColor: '#52c41a' }}>
          <Button
            type="primary"
            size="large"
            block
            onClick={onCheckout}
            disabled={items.length === 0}
            loading={isLoading}
            icon={<CreditCardOutlined />}
            style={{
              height: 48,
              fontSize: 16,
              fontWeight: 600,
              borderRadius: 8,
            }}
          >
            Checkout (F9)
          </Button>
        </Badge>
      </div>
    </div>
  );
}
