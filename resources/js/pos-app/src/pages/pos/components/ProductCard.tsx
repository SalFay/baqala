import React from 'react';
import { Card, Badge, Typography, Tag, Tooltip } from 'antd';
import { ShoppingCartOutlined, ExclamationCircleOutlined } from '@ant-design/icons';
import { useTheme } from '@/contexts/ThemeContext';

const { Text } = Typography;

interface Product {
  id: number;
  name: string;
  name_ar?: string;
  sku?: string;
  barcode?: string;
  type: 'simple' | 'variable';
  sale_price: number;
  compare_price?: number;
  cost_price: number;
  image_url?: string;
  track_inventory: boolean;
  stock_quantity: number;
  low_stock_threshold: number;
  variants?: Array<{
    id: number;
    name: string;
    sale_price: number;
    stock_quantity: number;
  }>;
}

interface ProductCardProps {
  product: Product;
  onClick: (product: Product) => void;
  showStock?: boolean;
  size?: 'small' | 'medium' | 'large';
  currency?: string;
}

export function ProductCard({
  product,
  onClick,
  showStock = true,
  size = 'medium',
  currency = 'SAR',
}: ProductCardProps) {
  const { isDark, colors, language } = useTheme();

  const displayName = language === 'ar' && product.name_ar ? product.name_ar : product.name;

  const isLowStock = product.track_inventory && product.stock_quantity <= product.low_stock_threshold;
  const isOutOfStock = product.track_inventory && product.stock_quantity <= 0;
  const hasDiscount = product.compare_price && product.compare_price > product.sale_price;
  const hasVariants = product.type === 'variable' && product.variants && product.variants.length > 0;

  // Size configurations
  const sizeConfig = {
    small: { width: 120, height: 140, imageHeight: 60, fontSize: 12, priceSize: 14 },
    medium: { width: 160, height: 180, imageHeight: 80, fontSize: 13, priceSize: 16 },
    large: { width: 200, height: 220, imageHeight: 100, fontSize: 14, priceSize: 18 },
  };

  const config = sizeConfig[size];

  const formatPrice = (price: number) => {
    return `${price.toFixed(2)} ${currency}`;
  };

  const cardContent = (
    <Card
      hoverable
      onClick={() => onClick(product)}
      style={{
        width: config.width,
        height: config.height,
        overflow: 'hidden',
        opacity: isOutOfStock ? 0.6 : 1,
        cursor: isOutOfStock ? 'not-allowed' : 'pointer',
        backgroundColor: isDark ? '#262626' : '#fff',
        borderColor: isDark ? '#434343' : '#f0f0f0',
      }}
      bodyStyle={{
        padding: 8,
        height: '100%',
        display: 'flex',
        flexDirection: 'column',
      }}
      bordered
    >
      {/* Product Image */}
      <div
        style={{
          height: config.imageHeight,
          backgroundColor: isDark ? '#1f1f1f' : '#f5f5f5',
          borderRadius: 4,
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'center',
          marginBottom: 8,
          overflow: 'hidden',
          position: 'relative',
        }}
      >
        {product.image_url ? (
          <img
            src={product.image_url}
            alt={displayName}
            style={{
              width: '100%',
              height: '100%',
              objectFit: 'cover',
            }}
          />
        ) : (
          <ShoppingCartOutlined
            style={{
              fontSize: 24,
              color: isDark ? '#595959' : '#bfbfbf',
            }}
          />
        )}

        {/* Variant Badge */}
        {hasVariants && (
          <Tag
            color="blue"
            style={{
              position: 'absolute',
              top: 4,
              right: 4,
              margin: 0,
              fontSize: 10,
            }}
          >
            {product.variants!.length} options
          </Tag>
        )}

        {/* Out of Stock Overlay */}
        {isOutOfStock && (
          <div
            style={{
              position: 'absolute',
              top: 0,
              left: 0,
              right: 0,
              bottom: 0,
              backgroundColor: 'rgba(0,0,0,0.5)',
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'center',
            }}
          >
            <Tag color="error">Out of Stock</Tag>
          </div>
        )}
      </div>

      {/* Product Name */}
      <Tooltip title={displayName}>
        <Text
          strong
          style={{
            fontSize: config.fontSize,
            lineHeight: 1.3,
            overflow: 'hidden',
            textOverflow: 'ellipsis',
            display: '-webkit-box',
            WebkitLineClamp: 2,
            WebkitBoxOrient: 'vertical',
            flex: 1,
            color: isDark ? '#fff' : '#000',
          }}
        >
          {displayName}
        </Text>
      </Tooltip>

      {/* Price Section */}
      <div
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          marginTop: 'auto',
        }}
      >
        <div>
          <Text
            strong
            style={{
              fontSize: config.priceSize,
              color: colors.primaryColor,
            }}
          >
            {formatPrice(product.sale_price)}
          </Text>
          {hasDiscount && (
            <Text
              delete
              type="secondary"
              style={{
                fontSize: config.fontSize - 2,
                marginLeft: 4,
              }}
            >
              {formatPrice(product.compare_price!)}
            </Text>
          )}
        </div>

        {/* Stock Indicator */}
        {showStock && product.track_inventory && !isOutOfStock && (
          <Tooltip title={`Stock: ${product.stock_quantity}`}>
            <Tag
              color={isLowStock ? 'warning' : 'success'}
              style={{ margin: 0, fontSize: 10 }}
              icon={isLowStock ? <ExclamationCircleOutlined /> : undefined}
            >
              {product.stock_quantity}
            </Tag>
          </Tooltip>
        )}
      </div>
    </Card>
  );

  // Wrap with discount badge if applicable
  if (hasDiscount) {
    const discountPercent = Math.round(
      ((product.compare_price! - product.sale_price) / product.compare_price!) * 100
    );
    return (
      <Badge.Ribbon
        text={`-${discountPercent}%`}
        color="red"
      >
        {cardContent}
      </Badge.Ribbon>
    );
  }

  return cardContent;
}

export default ProductCard;
