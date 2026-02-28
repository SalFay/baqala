import { useEffect, useRef, forwardRef, useImperativeHandle } from 'react'
import { Empty, Skeleton, theme } from 'antd'
import ProductCard from './ProductCard'

// Skeleton loading card
function SkeletonCard() {
  const { token } = theme.useToken()
  return (
    <div
      style={{
        background: token.colorBgContainer,
        borderRadius: 12,
        overflow: 'hidden',
        border: `1px solid ${token.colorBorderSecondary}`,
      }}
    >
      <Skeleton.Image active style={{ width: '100%', height: 100 }} />
      <div style={{ padding: 12 }}>
        <Skeleton.Input active size="small" style={{ width: '100%', marginBottom: 8 }} />
        <Skeleton.Button active size="small" style={{ width: '60%' }} />
      </div>
    </div>
  )
}

const ProductGrid = forwardRef(function ProductGrid({
  products,
  onAddProduct,
  loading,
  cartItems = [],
  selectedIndex = -1,
  onSelectedIndexChange,
  keyboardEnabled = false,
  onQuickView,
}, ref) {
  const gridRef = useRef(null)
  const columnsRef = useRef(4)

  // Create a map of product quantities in cart
  const cartQuantityMap = cartItems.reduce((acc, item) => {
    acc[item.product_id] = (acc[item.product_id] || 0) + item.quantity
    return acc
  }, {})

  // Calculate columns based on grid width
  useEffect(() => {
    const calculateColumns = () => {
      if (gridRef.current) {
        const width = gridRef.current.offsetWidth
        columnsRef.current = Math.max(2, Math.floor(width / 180))
      }
    }
    calculateColumns()
    window.addEventListener('resize', calculateColumns)
    return () => window.removeEventListener('resize', calculateColumns)
  }, [])

  // Scroll selected product into view
  useEffect(() => {
    if (selectedIndex >= 0 && gridRef.current) {
      const selectedElement = gridRef.current.querySelector(`[data-product-index="${selectedIndex}"]`)
      if (selectedElement) {
        selectedElement.scrollIntoView({ behavior: 'smooth', block: 'nearest' })
      }
    }
  }, [selectedIndex])

  // Expose navigation methods via ref
  useImperativeHandle(ref, () => ({
    navigateUp: () => {
      if (!products?.length) return
      const newIndex = Math.max(0, selectedIndex - columnsRef.current)
      onSelectedIndexChange?.(newIndex)
    },
    navigateDown: () => {
      if (!products?.length) return
      const newIndex = Math.min(products.length - 1, selectedIndex + columnsRef.current)
      onSelectedIndexChange?.(newIndex)
    },
    navigateLeft: () => {
      if (!products?.length) return
      const newIndex = Math.max(0, selectedIndex - 1)
      onSelectedIndexChange?.(newIndex)
    },
    navigateRight: () => {
      if (!products?.length) return
      const newIndex = Math.min(products.length - 1, selectedIndex + 1)
      onSelectedIndexChange?.(newIndex)
    },
    selectCurrent: () => {
      if (selectedIndex >= 0 && selectedIndex < products?.length) {
        const product = products[selectedIndex]
        if (product && product.in_stock) {
          onAddProduct(product)
        }
      }
    },
  }))

  if (loading) {
    return (
      <div
        style={{
          display: 'grid',
          gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
          gap: 12,
        }}
      >
        {[...Array(8)].map((_, i) => (
          <SkeletonCard key={i} />
        ))}
      </div>
    )
  }

  if (!products || products.length === 0) {
    return (
      <Empty
        description="No products found"
        style={{ padding: '60px 0' }}
      />
    )
  }

  return (
    <div
      ref={gridRef}
      className="pos-product-grid"
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
        gap: 12,
      }}
    >
      {products.map((product, index) => (
        <div key={product.id} data-product-index={index}>
          <ProductCard
            product={product}
            onAdd={onAddProduct}
            cartQuantity={cartQuantityMap[product.id] || 0}
            isSelected={keyboardEnabled && selectedIndex === index}
            onQuickView={onQuickView}
          />
        </div>
      ))}
    </div>
  )
})

export default ProductGrid
