import { Typography, theme } from 'antd'
import { ShoppingOutlined, CheckOutlined } from '@ant-design/icons'
import { formatCurrency } from '@/Helpers/formatters'

const { Text } = Typography

export default function ProductCard({ product, onAdd, loading, cartQuantity = 0, isSelected = false, onQuickView }) {
  const { token } = theme.useToken()
  const stockQty = product.stock_qty ?? product.stock
  const isOutOfStock = !product.in_stock || (stockQty !== null && stockQty <= 0)
  const isLowStock = stockQty !== null && stockQty > 0 && stockQty <= 10

  const handleContextMenu = (e) => {
    e.preventDefault()
    onQuickView?.(product)
  }

  const handleClick = () => {
    if (!isOutOfStock && !loading) {
      onAdd(product)
    }
  }

  return (
    <div
      className={`pos-product-card ${isSelected ? 'pos-product-card-selected' : ''}`}
      onClick={handleClick}
      onContextMenu={handleContextMenu}
      style={{
        position: 'relative',
        background: token.colorBgContainer,
        borderRadius: 12,
        overflow: 'hidden',
        cursor: isOutOfStock ? 'not-allowed' : 'pointer',
        opacity: isOutOfStock ? 0.6 : 1,
        transition: 'all 0.2s ease',
        border: `2px solid ${isSelected ? token.colorPrimary : cartQuantity > 0 ? token.colorSuccess : token.colorBorderSecondary}`,
        boxShadow: isSelected
          ? `0 0 0 3px ${token.colorPrimaryBg}`
          : cartQuantity > 0
          ? `0 0 0 2px ${token.colorSuccessBg}`
          : '0 1px 3px rgba(0,0,0,0.08)',
        transform: isSelected ? 'scale(1.02)' : undefined,
      }}
    >
      {/* Cart Quantity Badge */}
      {cartQuantity > 0 && (
        <div
          style={{
            position: 'absolute',
            top: 8,
            right: 8,
            zIndex: 10,
            background: token.colorSuccess,
            color: '#fff',
            borderRadius: 8,
            minWidth: 28,
            height: 28,
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            fontSize: 13,
            fontWeight: 600,
            padding: '0 8px',
            boxShadow: '0 2px 6px rgba(0,0,0,0.15)',
          }}
        >
          <CheckOutlined style={{ marginRight: cartQuantity > 1 ? 4 : 0, fontSize: 12 }} />
          {cartQuantity > 1 && cartQuantity}
        </div>
      )}

      {/* Out of Stock Overlay */}
      {isOutOfStock && (
        <div
          style={{
            position: 'absolute',
            top: 8,
            left: 8,
            zIndex: 10,
            background: token.colorError,
            color: '#fff',
            borderRadius: 6,
            padding: '4px 8px',
            fontSize: 10,
            fontWeight: 600,
            textTransform: 'uppercase',
            letterSpacing: 0.5,
          }}
        >
          Out of Stock
        </div>
      )}

      {/* Low Stock Badge */}
      {isLowStock && !isOutOfStock && (
        <div
          style={{
            position: 'absolute',
            top: 8,
            left: 8,
            zIndex: 10,
            background: token.colorWarning,
            color: '#fff',
            borderRadius: 6,
            padding: '4px 8px',
            fontSize: 10,
            fontWeight: 600,
          }}
        >
          {stockQty} left
        </div>
      )}

      {/* Product Image */}
      <div
        style={{
          height: 100,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          background: product.image_url ? 'transparent' : token.colorBgLayout,
          backgroundImage: product.image_url ? `url(${product.image_url})` : undefined,
          backgroundSize: 'cover',
          backgroundPosition: 'center',
        }}
      >
        {!product.image_url && (
          <ShoppingOutlined style={{ fontSize: 32, color: token.colorTextQuaternary }} />
        )}
      </div>

      {/* Product Info */}
      <div style={{ padding: '10px 12px' }}>
        <Text
          strong
          style={{
            fontSize: 13,
            lineHeight: 1.3,
            marginBottom: 6,
            height: 34,
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            display: '-webkit-box',
            WebkitLineClamp: 2,
            WebkitBoxOrient: 'vertical',
          }}
        >
          {product.name}
        </Text>

        <div
          style={{
            background: token.colorPrimaryBg,
            borderRadius: 8,
            padding: '6px 10px',
            textAlign: 'center',
          }}
        >
          <Text
            strong
            style={{
              fontSize: 16,
              color: token.colorPrimary,
            }}
          >
            {formatCurrency(product.price)}
          </Text>
        </div>
      </div>
    </div>
  )
}
