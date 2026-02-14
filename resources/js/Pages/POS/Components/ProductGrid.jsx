import { Card, Typography, Tag, Badge } from 'antd';
import { formatCurrency } from '@/Helpers/formatters';

const { Text } = Typography;

export default function ProductGrid({ products, onProductClick }) {
    return (
        <div className="product-grid">
            {products.map((product) => {
                const stock = product.stock_qty ?? product.stock ?? 999;
                const isLowStock = stock <= (product.low_stock_threshold || 10);
                const isOutOfStock = stock <= 0 && product.in_stock === false;

                return (
                    <Badge.Ribbon
                        key={product.id}
                        text={isOutOfStock ? 'Out of Stock' : isLowStock ? 'Low Stock' : null}
                        color={isOutOfStock ? 'red' : 'orange'}
                        style={{ display: isOutOfStock || isLowStock ? 'block' : 'none' }}
                    >
                        <Card
                            hoverable
                            className="product-card"
                            style={{ opacity: isOutOfStock ? 0.6 : 1 }}
                            cover={
                                <div
                                    style={{
                                        height: 100,
                                        background: product.image_url
                                            ? `url(${product.image_url}) center/cover no-repeat`
                                            : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                    }}
                                >
                                    {!product.image_url && (
                                        <Text style={{ color: '#fff', fontSize: 24, fontWeight: 'bold' }}>
                                            {product.name?.charAt(0)?.toUpperCase()}
                                        </Text>
                                    )}
                                </div>
                            }
                            bodyStyle={{ padding: 12 }}
                            onClick={() => !isOutOfStock && onProductClick(product, 1, product.default_variant_id)}
                        >
                            <Text strong ellipsis style={{ display: 'block', marginBottom: 4 }}>
                                {product.name}
                            </Text>
                            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                <Text type="secondary" style={{ fontSize: 11 }}>
                                    {product.category?.name || 'Uncategorized'}
                                </Text>
                                <Tag color="blue" style={{ margin: 0 }}>
                                    {formatCurrency(product.price)}
                                </Tag>
                            </div>
                            {product.sku && (
                                <Text type="secondary" style={{ fontSize: 10, display: 'block', marginTop: 4 }}>
                                    SKU: {product.sku}
                                </Text>
                            )}
                            {product.variants_count > 0 && (
                                <Tag color="purple" style={{ marginTop: 4, fontSize: 10 }}>
                                    {product.variants_count} variants
                                </Tag>
                            )}
                        </Card>
                    </Badge.Ribbon>
                );
            })}
        </div>
    );
}
