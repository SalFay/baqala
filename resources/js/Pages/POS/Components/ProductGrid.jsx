import { Empty, Spin } from 'antd'
import ProductCard from './ProductCard'

export default function ProductGrid({ products, onAddProduct, loading }) {
  if (loading) {
    return (
      <div style={{ textAlign: 'center', padding: '60px 0' }}>
        <Spin size="large" />
      </div>
    )
  }

  if (!products || products.length === 0) {
    return <Empty description="No products found" style={{ padding: '60px 0' }} />
  }

  return (
    <div
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(140px, 1fr))',
        gap: 12,
        padding: 4,
      }}
    >
      {products.map((product) => (
        <ProductCard key={product.id} product={product} onAdd={onAddProduct} />
      ))}
    </div>
  )
}
