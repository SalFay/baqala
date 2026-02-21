import { Typography, Divider } from 'antd'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function CartSummary({ summary }) {
  const { subtotal = 0, tax = 0, discount = 0, loyaltyDiscount = 0, total = 0 } = summary

  return (
    <div style={{ padding: '12px 0' }}>
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
        <Text type="secondary">Subtotal</Text>
        <Text>{formatCurrency(subtotal)}</Text>
      </div>

      {tax > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Tax</Text>
          <Text>{formatCurrency(tax)}</Text>
        </div>
      )}

      {discount > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Discount</Text>
          <Text type="success">-{formatCurrency(discount)}</Text>
        </div>
      )}

      {loyaltyDiscount > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Loyalty Discount</Text>
          <Text type="success">-{formatCurrency(loyaltyDiscount)}</Text>
        </div>
      )}

      <Divider style={{ margin: '12px 0' }} />

      <div style={{ display: 'flex', justifyContent: 'space-between' }}>
        <Text strong style={{ fontSize: 18 }}>Total</Text>
        <Text strong style={{ fontSize: 18, color: '#1890ff' }}>
          {formatCurrency(total)}
        </Text>
      </div>
    </div>
  )
}
