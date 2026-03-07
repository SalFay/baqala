import { Typography, Divider, Tag, theme } from 'antd'
import { CloseOutlined } from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text, Title } = Typography

export default function CartSummary({ summary, couponCode, onRemoveCoupon }) {
  const { token } = theme.useToken()
  const {
    subtotal = 0,
    tax = 0,
    tax_rate = 0,
    discount = 0,
    loyaltyDiscount = 0,
    couponDiscount = 0,
    total = 0
  } = summary

  return (
    <div
      style={{
        padding: '16px 0',
        borderTop: `1px solid ${token.colorBorderSecondary}`,
      }}
    >
      {/* Subtotal */}
      <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
        <Text type="secondary">Subtotal</Text>
        <Text>{formatCurrency(subtotal)}</Text>
      </div>

      {/* Tax */}
      {tax > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
          <Text type="secondary">Tax {tax_rate > 0 ? `(${tax_rate}%)` : ''}</Text>
          <Text>{formatCurrency(tax)}</Text>
        </div>
      )}

      {/* Discount */}
      {discount > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
          <Text type="secondary">Discount</Text>
          <Text style={{ color: token.colorSuccess }}>-{formatCurrency(discount)}</Text>
        </div>
      )}

      {/* Coupon Discount */}
      {(couponDiscount > 0 || couponCode) && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10, alignItems: 'center' }}>
          <div>
            <Text type="secondary">Coupon </Text>
            {couponCode && (
              <Tag
                color="green"
                closable
                onClose={(e) => { e.preventDefault(); onRemoveCoupon?.() }}
                style={{ marginLeft: 4 }}
              >
                {couponCode}
              </Tag>
            )}
          </div>
          {couponDiscount > 0 && (
            <Text style={{ color: token.colorSuccess }}>-{formatCurrency(couponDiscount)}</Text>
          )}
        </div>
      )}

      {/* Loyalty Discount */}
      {loyaltyDiscount > 0 && (
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 10 }}>
          <Text type="secondary">Loyalty Discount</Text>
          <Text style={{ color: token.colorSuccess }}>-{formatCurrency(loyaltyDiscount)}</Text>
        </div>
      )}

      <Divider style={{ margin: '12px 0' }} />

      {/* Total */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: '8px 12px',
          background: token.colorPrimaryBg,
          borderRadius: 8,
          marginBottom: 12,
        }}
      >
        <Title level={4} style={{ margin: 0 }}>Total</Title>
        <Title
          level={3}
          style={{
            margin: 0,
            color: token.colorPrimary,
            fontWeight: 700,
          }}
          className="cart-total-amount"
        >
          {formatCurrency(total)}
        </Title>
      </div>
    </div>
  )
}
