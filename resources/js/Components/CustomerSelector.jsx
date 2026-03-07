import { useState } from 'react'
import { Select, Spin, Space, Typography } from 'antd'
import { UserOutlined, PhoneOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import { debounce } from 'lodash'
import customerService from '@/Helpers/api/customerService'

const { Text } = Typography

export default function CustomerSelector({
  value,
  onChange,
  placeholder = 'Search customer...',
  disabled = false,
  allowClear = true,
  style = {},
  ...props
}) {
  const [search, setSearch] = useState('')

  const { data, isLoading } = useQuery({
    queryKey: ['customerSearch', search],
    queryFn: () => {
      if (search.length < 2) {
        return customerService.getAll({ limit: 20 }).then(res => res.data)
      }
      return customerService.search(search).then(res => res.data)
    },
    staleTime: 30000,
  })

  const customers = data?.data || data || []

  const handleSearch = debounce((value) => {
    setSearch(value)
  }, 300)

  const handleChange = (selectedValue, option) => {
    onChange?.(selectedValue, option)
  }

  return (
    <Select
      value={value}
      onChange={handleChange}
      placeholder={placeholder}
      disabled={disabled}
      allowClear={allowClear}
      showSearch
      filterOption={false}
      onSearch={handleSearch}
      loading={isLoading}
      style={{ width: '100%', ...style }}
      notFoundContent={isLoading ? <Spin size="small" /> : 'No customers found'}
      optionLabelProp="label"
      {...props}
    >
      {customers.map(customer => (
        <Select.Option
          key={customer.id}
          value={customer.id}
          label={customer.name}
          customer={customer}
        >
          <Space direction="vertical" size={0} style={{ width: '100%' }}>
            <Space>
              <UserOutlined />
              <Text strong>{customer.name}</Text>
            </Space>
            {customer.phone && (
              <Text type="secondary" style={{ fontSize: 12, marginLeft: 22 }}>
                <PhoneOutlined /> {customer.phone}
              </Text>
            )}
          </Space>
        </Select.Option>
      ))}
    </Select>
  )
}
