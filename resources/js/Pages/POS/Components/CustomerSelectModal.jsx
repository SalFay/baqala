import { useState, useEffect } from 'react'
import { Input, List, Avatar, Typography, Space, Empty, Spin, Button, Form, message, Divider, theme } from 'antd'
import { UserOutlined, SearchOutlined, CloseCircleOutlined, PlusOutlined, ArrowLeftOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import posService from '@/Helpers/api/posService'

const { Text, Title } = Typography
const { Search } = Input

export default function CustomerSelectModal({
  open,
  onClose,
  onSelect,
  currentCustomer,
  onRemove,
}) {
  const { token } = theme.useToken()
  const [search, setSearch] = useState('')
  const [debouncedSearch, setDebouncedSearch] = useState('')
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [form] = Form.useForm()
  const queryClient = useQueryClient()

  // Debounce search
  useEffect(() => {
    const timer = setTimeout(() => {
      setDebouncedSearch(search)
    }, 300)
    return () => clearTimeout(timer)
  }, [search])

  // Reset form when modal closes
  useEffect(() => {
    if (!open) {
      setShowCreateForm(false)
      form.resetFields()
    }
  }, [open, form])

  // Create customer mutation
  const createCustomerMutation = useMutation({
    mutationFn: (data) => posService.quickCreateCustomer(data),
    onSuccess: (response) => {
      const newCustomer = response.data.customer
      message.success('Customer created successfully')
      queryClient.invalidateQueries({ queryKey: ['customers-search'] })
      onSelect(newCustomer.id)
      onClose()
      setShowCreateForm(false)
      form.resetFields()
    },
    onError: (error) => {
      message.error(error.response?.data?.message || 'Failed to create customer')
    },
  })

  const handleCreateCustomer = async (values) => {
    createCustomerMutation.mutate(values)
  }

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
    <CustomModal
      title={showCreateForm ? "Create New Customer" : "Select Customer"}
      open={open}
      onCancel={onClose}
      footer={null}
      width={500}
    >
      {showCreateForm ? (
        // Create Customer Form
        <div>
          <Button
            type="text"
            icon={<ArrowLeftOutlined />}
            onClick={() => setShowCreateForm(false)}
            style={{ marginBottom: 16, paddingLeft: 0 }}
          >
            Back to search
          </Button>

          <Form
            form={form}
            layout="vertical"
            onFinish={handleCreateCustomer}
          >
            <Form.Item
              name="first_name"
              label="First Name"
              rules={[{ required: true, message: 'Please enter first name' }]}
            >
              <Input size="large" placeholder="John" />
            </Form.Item>

            <Form.Item
              name="last_name"
              label="Last Name"
            >
              <Input size="large" placeholder="Doe" />
            </Form.Item>

            <Form.Item
              name="phone"
              label="Phone Number"
              rules={[
                { required: true, message: 'Please enter phone number' },
                { pattern: /^[0-9+\-\s]+$/, message: 'Invalid phone number' },
              ]}
            >
              <Input size="large" placeholder="+966 5XX XXX XXXX" />
            </Form.Item>

            <Form.Item
              name="email"
              label="Email (Optional)"
              rules={[
                { type: 'email', message: 'Please enter a valid email' },
              ]}
            >
              <Input size="large" placeholder="john@example.com" />
            </Form.Item>

            <Button
              type="primary"
              htmlType="submit"
              size="large"
              block
              loading={createCustomerMutation.isPending}
              icon={<PlusOutlined />}
            >
              Create & Select Customer
            </Button>
          </Form>
        </div>
      ) : (
        // Search & Select Customer
        <>
          {currentCustomer && (
            <div
              style={{
                padding: 16,
                background: token.colorBgLayout,
                borderRadius: 8,
                marginBottom: 16,
                display: 'flex',
                justifyContent: 'space-between',
                alignItems: 'center',
              }}
            >
              <Space>
                <Avatar icon={<UserOutlined />} style={{ background: token.colorPrimary }} />
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

          <Space.Compact style={{ width: '100%', marginBottom: 16 }}>
            <Search
              placeholder="Search by name, phone, or email..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              prefix={<SearchOutlined />}
              size="large"
              allowClear
              style={{ flex: 1 }}
            />
            <Button
              type="primary"
              size="large"
              icon={<PlusOutlined />}
              onClick={() => setShowCreateForm(true)}
            >
              New
            </Button>
          </Space.Compact>

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
            <div style={{ textAlign: 'center', padding: 40 }}>
              <Empty description="No customers found" />
              <Button
                type="primary"
                icon={<PlusOutlined />}
                onClick={() => setShowCreateForm(true)}
                style={{ marginTop: 16 }}
              >
                Create New Customer
              </Button>
            </div>
          ) : (
            <Empty description="Start typing to search customers" style={{ padding: 40 }} />
          )}
        </>
      )}

      <style>{`
        .customer-list-item:hover {
          background: ${token.colorBgLayout};
          border-radius: 8px;
        }
      `}</style>
    </CustomModal>
  )
}
