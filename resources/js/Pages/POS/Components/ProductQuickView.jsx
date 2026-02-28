import { Typography, Space, Tag, Image, Descriptions, Table, Button, theme, Divider } from 'antd'
import { ShoppingOutlined, TagOutlined, InboxOutlined, BarcodeOutlined, ShoppingCartOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { formatCurrency } from '@/Helpers/formatters'

const { Title, Text } = Typography

export default function ProductQuickView({ open, onClose, product, onAddToCart, loading }) {
  const { token } = theme.useToken()
  if (!product) return null

  const stockQty = product.stock_qty ?? product.stock ?? 0
  const isOutOfStock = !product.in_stock || stockQty <= 0
  const hasVariants = product.has_variants || product.type === 'variable'
  const price = product.price || product.sale_price

  const variantColumns = [
    { title: 'Variant', dataIndex: 'name' },
    { title: 'SKU', dataIndex: 'sku', width: 100 },
    { title: 'Price', dataIndex: 'sale_price', width: 90, render: formatCurrency },
    {
      title: 'Stock', dataIndex: 'stock_qty', width: 70,
      render: (qty) => <Tag color={qty <= 0 ? 'red' : qty <= 10 ? 'orange' : 'green'}>{qty}</Tag>,
    },
    {
      title: '', width: 80,
      render: (_, v) => (
        <Button type="primary" size="small" onClick={() => onAddToCart?.(product, v)} disabled={v.stock_qty <= 0}>
          Add
        </Button>
      ),
    },
  ]

  return (
    <CustomModal
      title={<><ShoppingOutlined /> {product.name}</>}
      open={open}
      onCancel={onClose}
      width={550}
      footer={hasVariants ? null : (
        <Space>
          <Button onClick={onClose}>Close</Button>
          <Button type="primary" icon={<ShoppingCartOutlined />} onClick={() => onAddToCart?.(product)} loading={loading} disabled={isOutOfStock}>
            {isOutOfStock ? 'Out of Stock' : 'Add to Cart'}
          </Button>
        </Space>
      )}
    >
      <div style={{ display: 'flex', gap: 20 }}>
        <div style={{ width: 160, flexShrink: 0 }}>
          {product.image_url ? (
            <Image src={product.image_url} alt={product.name} style={{ borderRadius: 8, width: '100%' }} />
          ) : (
            <div style={{ height: 160, background: token.colorBgLayout, borderRadius: 8, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <ShoppingOutlined style={{ fontSize: 40, color: token.colorTextSecondary }} />
            </div>
          )}
          <div style={{ marginTop: 8, textAlign: 'center' }}>
            <Tag color={isOutOfStock ? 'red' : stockQty <= 10 ? 'orange' : 'green'} icon={!isOutOfStock && <InboxOutlined />}>
              {isOutOfStock ? 'Out of Stock' : `${stockQty} in stock`}
            </Tag>
          </div>
        </div>

        <div style={{ flex: 1 }}>
          <Title level={3} style={{ margin: 0, color: token.colorSuccess }}>{formatCurrency(price)}</Title>
          {product.compare_price > price && (
            <Space>
              <Text delete type="secondary">{formatCurrency(product.compare_price)}</Text>
              <Tag color="red">{Math.round(((product.compare_price - price) / product.compare_price) * 100)}% OFF</Tag>
            </Space>
          )}

          <Divider style={{ margin: '12px 0' }} />

          <Descriptions column={1} size="small">
            {product.sku && <Descriptions.Item label={<><TagOutlined /> SKU</>}>{product.sku}</Descriptions.Item>}
            {product.barcode && <Descriptions.Item label={<><BarcodeOutlined /> Barcode</>}>{product.barcode}</Descriptions.Item>}
            {product.category && <Descriptions.Item label="Category"><Tag>{product.category.name || product.category}</Tag></Descriptions.Item>}
          </Descriptions>

          {product.description && <Text type="secondary" style={{ fontSize: 12 }}>{product.description}</Text>}
        </div>
      </div>

      {hasVariants && product.variants?.length > 0 && (
        <>
          <Divider orientation="left" style={{ marginTop: 20 }}>Variants</Divider>
          <Table dataSource={product.variants} columns={variantColumns} rowKey="id" pagination={false} size="small" />
        </>
      )}
    </CustomModal>
  )
}
