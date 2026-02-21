import { Drawer, Descriptions, Table, Typography, Space, Tag, Statistic, Row, Col, Card, Spin, Alert } from 'antd'
import { UserOutlined, PhoneOutlined, MailOutlined, HomeOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import axios from 'axios'
import StatusBadge from '@/Components/StatusBadge'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Title, Text } = Typography

export default function CustomerDetailDrawer({
  open,
  onClose,
  customerId,
}) {
  const { data: customer, isLoading, error } = useQuery({
    queryKey: ['customer-detail', customerId],
    queryFn: async () => {
      const response = await axios.get(`/pos/customers/${customerId}`)
      return response.data.data
    },
    enabled: !!customerId && open,
  })

  const orderColumns = [
    {
      title: 'Order #',
      dataIndex: 'order_number',
      key: 'order_number',
    },
    {
      title: 'Total',
      dataIndex: 'total',
      key: 'total',
      render: formatCurrency,
    },
    {
      title: 'Status',
      dataIndex: 'status',
      key: 'status',
      render: (value) => <StatusBadge status={value} />,
    },
    {
      title: 'Date',
      dataIndex: 'created_at',
      key: 'created_at',
      render: formatDateTime,
    },
  ]

  return (
    <Drawer
      title="Customer Details"
      placement="right"
      onClose={onClose}
      open={open}
      width={500}
    >
      {isLoading ? (
        <div style={{ textAlign: 'center', padding: 60 }}>
          <Spin size="large" />
        </div>
      ) : error ? (
        <Alert type="error" message="Failed to load customer details" />
      ) : customer ? (
        <>
          {/* Header */}
          <div style={{ textAlign: 'center', marginBottom: 24 }}>
            <div
              style={{
                width: 80,
                height: 80,
                borderRadius: '50%',
                background: '#1890ff',
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                margin: '0 auto 16px',
              }}
            >
              <UserOutlined style={{ fontSize: 36, color: '#fff' }} />
            </div>
            <Title level={4} style={{ margin: 0 }}>{customer.full_name}</Title>
            <Space style={{ marginTop: 8 }}>
              <StatusBadge status={customer.status} />
            </Space>
          </div>

          {/* Stats */}
          <Row gutter={16} style={{ marginBottom: 24 }}>
            <Col span={12}>
              <Card size="small">
                <Statistic
                  title="Loyalty Points"
                  value={customer.loyalty_points || 0}
                  valueStyle={{ color: '#1890ff' }}
                />
              </Card>
            </Col>
            <Col span={12}>
              <Card size="small">
                <Statistic
                  title="Credit Balance"
                  value={customer.credit_balance || 0}
                  prefix="SAR"
                  precision={2}
                  valueStyle={{ color: customer.credit_balance > 0 ? '#52c41a' : '#000' }}
                />
              </Card>
            </Col>
          </Row>

          {/* Contact Info */}
          <Card title="Contact Information" size="small" style={{ marginBottom: 16 }}>
            <Space direction="vertical" size={8} style={{ width: '100%' }}>
              {customer.phone && (
                <div>
                  <PhoneOutlined style={{ marginRight: 8, color: '#1890ff' }} />
                  <Text>{customer.phone}</Text>
                </div>
              )}
              {customer.email && (
                <div>
                  <MailOutlined style={{ marginRight: 8, color: '#1890ff' }} />
                  <Text>{customer.email}</Text>
                </div>
              )}
              {(customer.address || customer.city) && (
                <div>
                  <HomeOutlined style={{ marginRight: 8, color: '#1890ff' }} />
                  <Text>
                    {[customer.address, customer.city].filter(Boolean).join(', ')}
                  </Text>
                </div>
              )}
            </Space>
          </Card>

          {/* Account Info */}
          <Card title="Account Details" size="small" style={{ marginBottom: 16 }}>
            <Descriptions column={1} size="small">
              <Descriptions.Item label="Credit Limit">
                {formatCurrency(customer.credit_limit || 0)}
              </Descriptions.Item>
              <Descriptions.Item label="Member Since">
                {formatDateTime(customer.created_at)}
              </Descriptions.Item>
            </Descriptions>
          </Card>

          {/* Recent Orders */}
          {customer.recent_orders?.length > 0 && (
            <Card title="Recent Orders" size="small">
              <Table
                dataSource={customer.recent_orders}
                columns={orderColumns}
                rowKey="id"
                pagination={false}
                size="small"
              />
            </Card>
          )}
        </>
      ) : null}
    </Drawer>
  )
}
