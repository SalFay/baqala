import { useState } from 'react'
import {
  Drawer,
  List,
  Typography,
  Tag,
  Space,
  Input,
  Button,
  Spin,
  Empty,
  Divider,
  theme,
} from 'antd'
import {
  SearchOutlined,
  PrinterOutlined,
  ClockCircleOutlined,
  CheckCircleOutlined,
  CloseCircleOutlined,
} from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import posService from '@/Helpers/api/posService'
import { formatCurrency, formatDateTime } from '@/Helpers/formatters'

const { Text, Title } = Typography
const { Search } = Input

// Order status colors
const STATUS_COLORS = {
  completed: 'green',
  cancelled: 'red',
  pending: 'orange',
  refunded: 'purple',
}

// Order status icons
const STATUS_ICONS = {
  completed: <CheckCircleOutlined />,
  cancelled: <CloseCircleOutlined />,
  pending: <ClockCircleOutlined />,
}

export default function RecentOrdersDrawer({ open, onClose, onReprintReceipt }) {
  const { token } = theme.useToken()
  const [search, setSearch] = useState('')
  const [selectedOrder, setSelectedOrder] = useState(null)

  const { data: orders, isLoading } = useQuery({
    queryKey: ['recent-orders'],
    queryFn: async () => {
      const response = await posService.getRecentOrders(20)
      return response.data.data || response.data || []
    },
    enabled: open,
  })

  // Filter orders by search
  const filteredOrders = orders?.filter(order => {
    if (!search) return true
    const searchLower = search.toLowerCase()
    return (
      order.order_number?.toLowerCase().includes(searchLower) ||
      order.customer_name?.toLowerCase().includes(searchLower) ||
      formatCurrency(order.total).includes(search)
    )
  }) || []

  // Fetch order detail when selected
  const { data: orderDetail, isLoading: isLoadingDetail } = useQuery({
    queryKey: ['order-detail', selectedOrder?.id],
    queryFn: async () => {
      const response = await posService.getOrderDetail(selectedOrder.id)
      return response.data
    },
    enabled: !!selectedOrder?.id,
  })

  const handlePrintReceipt = async (order) => {
    try {
      const response = await posService.getOrderReceipt(order.id)
      onReprintReceipt?.(order, response.data)
    } catch (error) {
      console.error('Failed to fetch receipt:', error)
    }
  }

  return (
    <Drawer
      title={
        <Space>
          <ClockCircleOutlined />
          Recent Orders
        </Space>
      }
      placement="right"
      onClose={() => {
        setSelectedOrder(null)
        onClose()
      }}
      open={open}
      width={480}
    >
      {/* Search */}
      <Search
        placeholder="Search by order #, customer..."
        value={search}
        onChange={(e) => setSearch(e.target.value)}
        allowClear
        prefix={<SearchOutlined />}
        style={{ marginBottom: 16 }}
      />

      {isLoading ? (
        <div style={{ textAlign: 'center', padding: 40 }}>
          <Spin />
          <Text type="secondary" style={{ display: 'block', marginTop: 12 }}>
            Loading orders...
          </Text>
        </div>
      ) : filteredOrders.length === 0 ? (
        <Empty description="No orders found" />
      ) : selectedOrder ? (
        // Order Detail View
        <div>
          <Button
            type="text"
            onClick={() => setSelectedOrder(null)}
            style={{ marginBottom: 16, paddingLeft: 0 }}
          >
            Back to list
          </Button>

          {isLoadingDetail ? (
            <div style={{ textAlign: 'center', padding: 40 }}>
              <Spin />
            </div>
          ) : orderDetail ? (
            <div>
              <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                <Title level={4} style={{ margin: 0 }}>
                  #{orderDetail.order_number || selectedOrder.order_number}
                </Title>
                <Tag color={STATUS_COLORS[orderDetail.status || 'completed']}>
                  {orderDetail.status || 'completed'}
                </Tag>
              </div>

              <div style={{ marginTop: 16, marginBottom: 16 }}>
                <Text type="secondary">Date: </Text>
                <Text>{formatDateTime(orderDetail.created_at || selectedOrder.created_at)}</Text>
                <br />
                {orderDetail.customer_name && (
                  <>
                    <Text type="secondary">Customer: </Text>
                    <Text>{orderDetail.customer_name}</Text>
                    <br />
                  </>
                )}
                <Text type="secondary">Payment: </Text>
                <Text style={{ textTransform: 'capitalize' }}>{orderDetail.payment_type}</Text>
              </div>

              <Divider style={{ margin: '12px 0' }} />

              {/* Items */}
              <Text strong style={{ display: 'block', marginBottom: 8 }}>Items</Text>
              {orderDetail.items?.map((item, index) => (
                <div
                  key={index}
                  style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    padding: '8px 0',
                    borderBottom: `1px solid ${token.colorBorderSecondary}`,
                  }}
                >
                  <div>
                    <Text>{item.product_name || item.display_name}</Text>
                    <br />
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      {item.quantity} x {formatCurrency(item.unit_price || item.sale_price)}
                    </Text>
                  </div>
                  <Text strong>{formatCurrency(item.line_total)}</Text>
                </div>
              ))}

              <Divider style={{ margin: '12px 0' }} />

              {/* Totals */}
              <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                <Text type="secondary">Subtotal</Text>
                <Text>{formatCurrency(orderDetail.sub_total)}</Text>
              </div>
              {orderDetail.tax_amount > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                  <Text type="secondary">Tax</Text>
                  <Text>{formatCurrency(orderDetail.tax_amount)}</Text>
                </div>
              )}
              {orderDetail.discount > 0 && (
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 4 }}>
                  <Text type="secondary">Discount</Text>
                  <Text type="success">-{formatCurrency(orderDetail.discount)}</Text>
                </div>
              )}
              <div style={{ display: 'flex', justifyContent: 'space-between', marginTop: 8 }}>
                <Title level={4} style={{ margin: 0 }}>Total</Title>
                <Title level={4} style={{ margin: 0, color: token.colorPrimary }}>
                  {formatCurrency(orderDetail.total)}
                </Title>
              </div>

              {/* Actions */}
              <div style={{ marginTop: 24 }}>
                <Button
                  type="primary"
                  icon={<PrinterOutlined />}
                  block
                  onClick={() => handlePrintReceipt(orderDetail)}
                >
                  Print Receipt
                </Button>
              </div>
            </div>
          ) : null}
        </div>
      ) : (
        // Orders List
        <List
          dataSource={filteredOrders}
          renderItem={(order) => (
            <List.Item
              onClick={() => setSelectedOrder(order)}
              style={{
                cursor: 'pointer',
                padding: '12px 8px',
                borderRadius: 8,
              }}
              className="order-list-item"
              actions={[
                <Button
                  key="print"
                  type="text"
                  icon={<PrinterOutlined />}
                  onClick={(e) => {
                    e.stopPropagation()
                    handlePrintReceipt(order)
                  }}
                />,
              ]}
            >
              <List.Item.Meta
                title={
                  <Space>
                    <Text strong>#{order.order_number}</Text>
                    <Tag
                      color={STATUS_COLORS[order.status]}
                      icon={STATUS_ICONS[order.status]}
                      style={{ marginLeft: 'auto' }}
                    >
                      {order.status}
                    </Tag>
                  </Space>
                }
                description={
                  <Space direction="vertical" size={0}>
                    <Text type="secondary" style={{ fontSize: 12 }}>
                      {formatDateTime(order.created_at || order.date)}
                    </Text>
                    {order.customer_name && (
                      <Text type="secondary" style={{ fontSize: 12 }}>
                        {order.customer_name}
                      </Text>
                    )}
                  </Space>
                }
              />
              <Title level={5} style={{ margin: 0, color: token.colorPrimary }}>
                {formatCurrency(order.total)}
              </Title>
            </List.Item>
          )}
          style={{ maxHeight: 'calc(100vh - 200px)', overflow: 'auto' }}
        />
      )}

      <style>{`
        .order-list-item:hover {
          background: ${token.colorBgLayout};
        }
      `}</style>
    </Drawer>
  )
}
