import { Card, Typography, Tag } from 'antd';

const { Text } = Typography;

export default function ProductGrid({ products, onProductClick }) {
  return (
    <div className="product-grid">
      {products.map((product) => (
        <Card
          key={product.id}
          hoverable
          className="product-card"
          cover={
            <div
              style={{
                height: 100,
                background: `url(${product.image}) center/cover no-repeat`,
                backgroundColor: '#f5f5f5',
              }}
            />
          }
          bodyStyle={{ padding: 12 }}
          onClick={() => {
            if (product.type === 'variable' && product.variants?.length > 0) {
              // For variable products, we could show a variant selector
              // For now, just add the first variant
              onProductClick(product, product.variants[0]);
            } else {
              onProductClick(product, null);
            }
          }}
        >
          <Text strong ellipsis style={{ display: 'block', marginBottom: 4 }}>
            {product.name}
          </Text>
          <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
            <Text type="secondary" style={{ fontSize: 12 }}>
              {product.category?.name}
            </Text>
            <Tag color="blue">{product.sale_price.toFixed(2)}</Tag>
          </div>
          {product.type === 'variable' && (
            <Tag color="purple" style={{ marginTop: 4 }}>
              {product.variants?.length || 0} variants
            </Tag>
          )}
        </Card>
      ))}
    </div>
  );
}
