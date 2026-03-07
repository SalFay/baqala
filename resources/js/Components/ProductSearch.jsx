import { useState } from 'react'
import { AutoComplete, Input, Space, Typography, Tag } from 'antd'
import { SearchOutlined, BarcodeOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import { debounce } from 'lodash'
import productService from '@/Helpers/api/productService'

const { Text } = Typography

export default function ProductSearch({
  onSelect,
  placeholder = 'Search products by name, SKU, or barcode...',
  style = {},
  autoFocus = false,
}) {
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['productSearch', search],
    queryFn: () => {
      if (search.length < 2) return { data: [] }
      return productService.search(search).then(res => res.data)
    },
    enabled: search.length >= 2,
    staleTime: 30000,
  })

  const products = data?.data || data || []

  const handleSearch = debounce((value) => {
    setSearch(value)
  }, 300)

  const handleSelect = (value, option) => {
    if (option?.product) {
      onSelect?.(option.product)
      setSearch('')
    }
  }

  const options = products.map(product => ({
    value: product.id.toString(),
    label: (
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
        <Space direction="vertical" size={0}>
          <Text strong>{product.name}</Text>
          <Space size={4}>
            {product.sku && (
              <Text type="secondary" style={{ fontSize: 12 }}>
                SKU: {product.sku}
              </Text>
            )}
            {product.barcode && (
              <Text type="secondary" style={{ fontSize: 12 }}>
                <BarcodeOutlined /> {product.barcode}
              </Text>
            )}
          </Space>
        </Space>
        <Space>
          <Tag color={product.stock_quantity > 0 ? 'green' : 'red'}>
            {product.stock_quantity || 0} in stock
          </Tag>
          <Text strong>${parseFloat(product.selling_price || 0).toFixed(2)}</Text>
        </Space>
      </div>
    ),
    product: product,
  }))

  return (
    <AutoComplete
      style={{ width: '100%', ...style }}
      options={options}
      onSearch={handleSearch}
      onSelect={handleSelect}
      value={search}
      onChange={setSearch}
      notFoundContent={search.length >= 2 && !isLoading ? 'No products found' : null}
    >
      <Input
        placeholder={placeholder}
        prefix={<SearchOutlined />}
        autoFocus={autoFocus}
        size="large"
        allowClear
      />
    </AutoComplete>
  )
}
