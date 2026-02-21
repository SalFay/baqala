import { useState, useEffect } from 'react'
import { Modal, Input, List, Avatar, Typography, Space, Empty, Spin, Button } from 'antd'
import { UserOutlined, SearchOutlined, CloseCircleOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import posService from '@/Helpers/api/posService'

const { Text } = Typography
const { Search } = Input

export default function CustomerSelectModal({
  open,
  onClose,
  onSelect,
  currentCustomer,
  onRemove,
}) {
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')

  // Debounce search
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search)
    }, 300)
    return () => clearTimeout(timer)
  }, [search])

  const { data: customers, isLoading } = useQuery({
    queryKey: ['customers-search', debouncedSearch],
    queryFn: async () => {
      if (!debouncedSearch) return []
      const response = await posService.searchCustomers(debouncedSearch)
      return response.data.data || []
    },
    enabled: open && debouncedSearch.length > 0,
  })

  const handleSelect = (customer) => {
    onSelect(customer.id)
    onClose()
    setSearch('')
  }

  const handleRemove = () => {
    onRemove()
    onClose()
  }

  return (
    <Modal
      title="Select Customer"
      open={open}
      onCancel={onClose}
      footer={null}
      width={500}
      destroyOnClose
    >
      {currentCustomer && (
        <div
          style={{
            padding: 16,
            background: '#f5f5f5',
            borderRadius: 8,
            marginBottom: 16,
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
          }}
        >
          <Space>
            <Avatar icon={<UserOutlined />} />
            <div>
              <Text strong>{currentCustomer.full_name}</Text>
              <br />
              <Text type="secondary">{currentCustomer.phone || currentCustomer.email}</Text>
            </div>
          </Space>
          <Button
            type="text"
            danger
            icon={<CloseCircleOutlined />}
            onClick={handleRemove}
          >
            Remove
          </Button>
        </div>
      )}

      <Search
        placeholder="Search by name, phone, or email..."
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        prefix={<SearchOutlined />}
        size="large"
        allowClear
        style={{ marginBottom: 16 }}
      />

      {isLoading ? (
        <div style={{ textAlign: 'center', padding: 40 }}>
          <Spin />
        </div>
      ) : customers && customers.length > 0 ? (
        <List
          dataSource={customers}
          renderItem={(customer) => (
            <List.Item
              onClick={() => handleSelect(customer)}
              style={{ cursor: 'pointer', padding: '12px 8px' }}
              className="customer-list-item"
            >
              <List.Item.Meta
                avatar={<Avatar icon={<UserOutlined />} />}
                title={customer.full_name}
                description={
                  <Space split="|">
                    {customer.phone && <span>{customer.phone}</span>}
                    {customer.email && <span>{customer.email}</span>}
                    {customer.loyalty_points > 0 && (
                      <span>{customer.loyalty_points} points</span>
                    )}
                  </Space>
                }
              />
            </List.Item>
          )}
          style={{ maxHeight: 300, overflow: 'auto' }}
        />
      ) : search.length > 0 ? (
        <Empty description="No customers found" style={{ padding: 40 }} />
      ) : (
        <Empty description="Start typing to search customers" style={{ padding: 40 }} />
      )}

      <style>{`
        .customer-list-item:hover {
          background: #f5f5f5;
          border-radius: 8px;
        }
      `}</style>
    </Modal>
  )
}
