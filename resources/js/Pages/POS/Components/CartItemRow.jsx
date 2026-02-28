import { Typography, Button, InputNumber, theme } from 'antd'
import { DeleteOutlined, MinusOutlined, PlusOutlined, ShoppingOutlined } from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function CartItemRow({ item, onUpdateQuantity, onRemove, loading, isSelected = false }) {
  const { token } = theme.useToken()

  const handleDecrease = () => {
    if (item.quantity > 1) {
      onUpdateQuantity(item.id, item.quantity - 1)
    } else {
      onRemove(item.id)
    }
  }

  const handleIncrease = () => {
    onUpdateQuantity(item.id, item.quantity + 1)
  }

  return (
    <div
      style={{
        display: 'flex',
        alignItems: 'center',
        gap: 12,
        padding: 12,
        borderRadius: 10,
        marginBottom: 8,
        background: isSelected ? token.colorPrimaryBg : token.colorBgLayout,
        border: `2px solid ${isSelected ? token.colorPrimary : 'transparent'}`,
        transition: 'all 0.2s ease',
      }}
    >
      {/* Product Image */}
      <div
        style={{
          width: 48,
          height: 48,
          borderRadius: 8,
          background: item.image_url ? `url(${item.image_url}) center/cover` : token.colorBgContainer,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          flexShrink: 0,
          border: `1px solid ${token.colorBorderSecondary}`,
        }}
      >
        {!item.image_url && <ShoppingOutlined style={{ color: token.colorTextQuaternary }} />}
      </div>

      {/* Product Info */}
      <div style={{ flex: 1, minWidth: 0 }}>
        <Text strong ellipsis style={{ display: 'block', fontSize: 13, marginBottom: 2 }}>
          {item.product_name}
        </Text>
        <Text type="secondary" style={{ fontSize: 12 }}>
          {formatCurrency(item.unit_price)} each
        </Text>
      </div>

      {/* Quantity Controls */}
      <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
        <Button
          size="small"
          icon={<MinusOutlined />}
          onClick={handleDecrease}
          disabled={loading}
          style={{ width: 28, height: 28, borderRadius: 6 }}
        />
        <InputNumber
          size="small"
          min={1}
          value={item.quantity}
          onChange={(val) => val && onUpdateQuantity(item.id, val)}
          style={{ width: 44, textAlign: 'center' }}
          controls={false}
          disabled={loading}
        />
        <Button
          size="small"
          icon={<PlusOutlined />}
          onClick={handleIncrease}
          disabled={loading}
          type="primary"
          style={{ width: 28, height: 28, borderRadius: 6 }}
        />
      </div>

      {/* Line Total */}
      <Text strong style={{ width: 70, textAlign: 'right', color: token.colorPrimary, fontSize: 14 }}>
        {formatCurrency(item.line_total)}
      </Text>

      {/* Remove Button */}
      <Button
        type="text"
        danger
        icon={<DeleteOutlined />}
        onClick={() => onRemove(item.id)}
        disabled={loading}
        size="small"
        style={{ width: 28, height: 28, borderRadius: 6 }}
      />
    </div>
  )
}
