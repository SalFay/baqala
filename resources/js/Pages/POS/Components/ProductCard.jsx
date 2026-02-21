import { Card, Typography, Badge, theme } from 'antd'
import { ShoppingOutlined } from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function ProductCard({ product, onAdd, loading }) {
  const { token } = theme.useToken()
  const stockQty = product.stock_qty ?? product.stock
  const isOutOfStock = !product.in_stock || (stockQty !== null && stockQty <= 0)

  return (
    <Badge.Ribbon
      text={isOutOfStock ? 'Out of Stock' : null}
      color="red"
      style={{ display: isOutOfStock ? 'block' : 'none' }}
    >
      <Card
        hoverable
        size="small"
        onClick={() => !isOutOfStock && !loading && onAdd(product)}
        style={{
          cursor: isOutOfStock ? 'not-allowed' : 'pointer',
          opacity: isOutOfStock ? 0.6 : 1,
          height: '100%',
          transition: 'all 0.2s ease',
          borderColor: 'transparent',
        }}
        cover={
          product.image_url ? (
            <div
              style={{
                height: 90,
                backgroundImage: `url(${product.image_url})`,
                backgroundSize: 'cover',
                backgroundPosition: 'center',
                borderRadius: '8px 8px 0 0',
              }}
            />
          ) : (
            <div
              style={{
                height: 90,
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                background: token.colorBgLayout,
                borderRadius: '8px 8px 0 0',
                fontSize: 32,
                color: token.colorTextQuaternary,
              }}
            >
              <ShoppingOutlined />
            </div>
          )
        }
        styles={{ body: { padding: '10px 12px' } }}
      >
        <Text
          strong
          ellipsis={{ tooltip: product.name }}
          style={{ display: 'block', marginBottom: 6, fontSize: 13, lineHeight: 1.3 }}
        >
          {product.name}
        </Text>
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
          <Text type="success" strong style={{ fontSize: 15 }}>
            {formatCurrency(product.price)}
          </Text>
          {stockQty !== null && (
            <Text
              type="secondary"
              style={{
                fontSize: 11,
                padding: '2px 6px',
                background: stockQty <= 10 ? token.colorWarningBg : token.colorBgLayout,
                borderRadius: 4,
                color: stockQty <= 10 ? token.colorWarning : token.colorTextSecondary,
              }}
            >
              {stockQty}
            </Text>
          )}
        </div>
      </Card>
    </Badge.Ribbon>
  )
}
