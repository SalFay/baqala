import { Card, Typography, Tag, Image, Empty, Badge } from 'antd';
import { PictureOutlined, TagsOutlined } from '@ant-design/icons';

const { Text } = Typography;

const NoImagePlaceholder = () => (
  <div
    style={{
      height: 120,
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      background: 'linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%)',
      color: '#bfbfbf',
    }}
  >
    <PictureOutlined style={{ fontSize: 40 }} />
  </div>
);

export default function ProductGrid({ products, onProductClick }) {
  if (!products?.length) {
    return (
      <div style={{ padding: 40, textAlign: 'center' }}>
        <Empty description="No products found" />
      </div>
    );
  }

  return (
    <div
      style={{
        display: 'grid',
        gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
        gap: 16,
        padding: '8px 0',
      }}
    >
      {products.map((product) => {
        const price = product.sale_price ?? product.price ?? 0;
        const hasImage = product.image || product.image_url;
        const hasVariants = product.type === 'variable' && product.variants?.length > 0;
        const isOutOfStock = product.stock_quantity <= 0;

        const cardContent = (
          <Card
            hoverable
            size="small"
            style={{
              borderRadius: 12,
              overflow: 'hidden',
              transition: 'all 0.3s ease',
              opacity: isOutOfStock ? 0.6 : 1,
            }}
            styles={{
              body: { padding: 12 },
            }}
            cover={
              hasImage ? (
                <Image
                  preview={false}
                  height={120}
                  width="100%"
                  style={{ objectFit: 'cover' }}
                  src={product.image_url || product.image}
                  fallback="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=="
                  alt={product.name}
                />
              ) : (
                <NoImagePlaceholder />
              )
            }
            onClick={() => {
              if (!isOutOfStock) {
                if (hasVariants) {
                  onProductClick(product, product.variants[0]);
                } else {
                  onProductClick(product, null);
                }
              }
            }}
          >
            <Text
              strong
              ellipsis={{ rows: 2 }}
              style={{
                display: 'block',
                fontSize: 14,
                lineHeight: '1.3',
                minHeight: 36,
                marginBottom: 8,
              }}
            >
              {product.name}
            </Text>

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
              <Text type="secondary" style={{ fontSize: 12 }} ellipsis>
                {product.category?.name || 'Uncategorized'}
              </Text>
              <Text
                strong
                style={{
                  fontSize: 16,
                  color: '#1890ff',
                  fontWeight: 700,
                }}
              >
                {Number(price).toFixed(2)}
              </Text>
            </div>

            {hasVariants && (
              <Tag
                icon={<TagsOutlined />}
                color="purple"
                style={{ marginTop: 8, fontSize: 11 }}
              >
                {product.variants.length} options
              </Tag>
            )}

            {isOutOfStock && (
              <Tag color="error" style={{ marginTop: 8, fontSize: 11 }}>
                Out of Stock
              </Tag>
            )}
          </Card>
        );

        return (
          <div key={product.id}>
            {isOutOfStock ? (
              <Badge.Ribbon text="Sold Out" color="red">
                {cardContent}
              </Badge.Ribbon>
            ) : (
              cardContent
            )}
          </div>
        );
      })}
    </div>
  );
}
