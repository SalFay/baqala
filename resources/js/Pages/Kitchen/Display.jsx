import { useState } from 'react'
import { Head } from '@inertiajs/react'
import {
  Card,
  Select,
  Button,
  Space,
  Tag,
  Badge,
  Row,
  Col,
  Statistic,
  Typography,
  Spin,
  Empty,
  Tooltip,
} from 'antd'
import {
  ClockCircleOutlined,
  CheckOutlined,
  FireOutlined,
  ReloadOutlined,
  SoundOutlined,
  TableOutlined,
  UserOutlined,
} from '@ant-design/icons'
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query'
import {
  fetchKitchenOrders,
  startPreparing,
  markReady,
  markServed,
  bumpOrder,
} from '@/Helpers/api/restaurantService'

const { Text, Title } = Typography

const priorityColors = {
  low: '#52c41a',
  normal: '#1890ff',
  high: '#fa8c16',
  urgent: '#ff4d4f',
}

const statusColors = {
  pending: '#faad14',
  preparing: '#1890ff',
  ready: '#52c41a',
  served: '#d9d9d9',
}

export default function KitchenDisplay() {
  const [selectedStation, setSelectedStation] = useState(null)
  const queryClient = useQueryClient()

  // Fetch kitchen orders
  const { data, isLoading, refetch } = useQuery({
    queryKey: ['kitchenOrders', selectedStation],
    queryFn: () => fetchKitchenOrders({ station: selectedStation }).then(res => res.data),
    refetchInterval: 10000, // Auto-refresh every 10 seconds
  })

  const pendingOrders = data?.pending || []
  const readyOrders = data?.ready || []
  const statistics = data?.statistics || {}
  const stations = data?.stations || []

  // Mutations
  const startMutation = useMutation({
    mutationFn: (id) => startPreparing(id),
    onSuccess: () => {
      queryClient.invalidateQueries(['kitchenOrders'])
    },
  })

  const readyMutation = useMutation({
    mutationFn: (id) => markReady(id),
    onSuccess: () => {
      queryClient.invalidateQueries(['kitchenOrders'])
      playNotificationSound()
    },
  })

  const servedMutation = useMutation({
    mutationFn: (id) => markServed(id),
    onSuccess: () => {
      queryClient.invalidateQueries(['kitchenOrders'])
    },
  })

  const bumpMutation = useMutation({
    mutationFn: (orderId) => bumpOrder(orderId),
    onSuccess: () => {
      queryClient.invalidateQueries(['kitchenOrders'])
      playNotificationSound()
    },
  })

  const playNotificationSound = () => {
    // Play sound notification when order is ready
    const audio = new Audio('/sounds/order-ready.mp3')
    audio.play().catch(() => {})
  }

  const getElapsedColor = (minutes) => {
    if (minutes >= 20) return '#ff4d4f'
    if (minutes >= 10) return '#fa8c16'
    return '#52c41a'
  }

  const OrderCard = ({ order, showBump = false }) => (
    <Card
      size="small"
      style={{
        marginBottom: 16,
        borderLeft: `4px solid ${order.items?.[0]?.priority_color || '#1890ff'}`,
      }}
      title={
        <Space style={{ width: '100%', justifyContent: 'space-between' }}>
          <Space>
            <Badge color={priorityColors[order.items?.[0]?.priority || 'normal']} />
            <Text strong>#{order.order_number}</Text>
          </Space>
          <Space>
            {order.table && (
              <Tag icon={<TableOutlined />}>{order.table}</Tag>
            )}
            <Tooltip title="Elapsed time">
              <Tag color={getElapsedColor(order.elapsed_time)} icon={<ClockCircleOutlined />}>
                {order.elapsed_time}m
              </Tag>
            </Tooltip>
          </Space>
        </Space>
      }
      extra={
        showBump && (
          <Button
            type="primary"
            size="small"
            icon={<CheckOutlined />}
            onClick={() => bumpMutation.mutate(order.order_id)}
            loading={bumpMutation.isPending}
          >
            Bump All
          </Button>
        )
      }
    >
      <div style={{ marginBottom: 8 }}>
        <Space>
          <UserOutlined />
          <Text type="secondary">{order.customer}</Text>
          <Text type="secondary">@ {order.created_at}</Text>
        </Space>
      </div>

      <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
        {order.items?.map(item => (
          <div
            key={item.id}
            style={{
              display: 'flex',
              alignItems: 'center',
              justifyContent: 'space-between',
              padding: 8,
              backgroundColor: '#fafafa',
              borderRadius: 4,
            }}
          >
            <Space>
              <Badge
                count={item.quantity}
                style={{ backgroundColor: '#1890ff' }}
                showZero
              />
              <Text strong>{item.product_name}</Text>
              {item.notes && (
                <Text type="secondary" style={{ fontSize: 12 }}>
                  ({item.notes})
                </Text>
              )}
            </Space>

            <Space>
              {item.station && (
                <Tag>{item.station}</Tag>
              )}

              {item.status === 'pending' && (
                <Button
                  size="small"
                  type="primary"
                  icon={<FireOutlined />}
                  onClick={() => startMutation.mutate(item.id)}
                  loading={startMutation.isPending}
                >
                  Start
                </Button>
              )}

              {item.status === 'preparing' && (
                <Button
                  size="small"
                  type="primary"
                  style={{ backgroundColor: '#52c41a' }}
                  icon={<CheckOutlined />}
                  onClick={() => readyMutation.mutate(item.id)}
                  loading={readyMutation.isPending}
                >
                  Ready
                </Button>
              )}

              {item.status === 'ready' && (
                <Tag color="green" icon={<CheckOutlined />}>
                  Ready
                </Tag>
              )}
            </Space>
          </div>
        ))}
      </div>
    </Card>
  )

  const ReadyOrderCard = ({ order }) => (
    <Card
      size="small"
      style={{
        marginBottom: 12,
        backgroundColor: '#f6ffed',
        borderColor: '#b7eb8f',
      }}
    >
      <Space style={{ width: '100%', justifyContent: 'space-between' }}>
        <Space>
          <SoundOutlined style={{ color: '#52c41a' }} />
          <Text strong>#{order.order_number}</Text>
          {order.table && <Tag>{order.table}</Tag>}
        </Space>
        <Space>
          {order.items?.map(item => (
            <Button
              key={item.id}
              size="small"
              onClick={() => servedMutation.mutate(item.id)}
              loading={servedMutation.isPending}
            >
              Served
            </Button>
          ))}
        </Space>
      </Space>
    </Card>
  )

  if (isLoading) {
    return (
      <div style={{ display: 'flex', justifyContent: 'center', alignItems: 'center', height: '80vh' }}>
        <Spin size="large" />
      </div>
    )
  }

  return (
    <>
      <Head title="Kitchen Display" />

      <div style={{ padding: 16, backgroundColor: '#141414', minHeight: '100vh' }}>
        {/* Header */}
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col flex="auto">
            <Title level={3} style={{ color: '#fff', margin: 0 }}>
              Kitchen Display System
            </Title>
          </Col>
          <Col>
            <Space>
              <Select
                value={selectedStation}
                onChange={setSelectedStation}
                placeholder="All Stations"
                style={{ width: 150 }}
                allowClear
              >
                {stations.map(station => (
                  <Select.Option key={station} value={station}>
                    {station}
                  </Select.Option>
                ))}
              </Select>
              <Button icon={<ReloadOutlined />} onClick={() => refetch()}>
                Refresh
              </Button>
            </Space>
          </Col>
        </Row>

        {/* Statistics */}
        <Row gutter={16} style={{ marginBottom: 16 }}>
          <Col xs={12} sm={6}>
            <Card size="small">
              <Statistic
                title="Pending"
                value={statistics.pending || 0}
                valueStyle={{ color: '#faad14' }}
              />
            </Card>
          </Col>
          <Col xs={12} sm={6}>
            <Card size="small">
              <Statistic
                title="Preparing"
                value={statistics.preparing || 0}
                valueStyle={{ color: '#1890ff' }}
              />
            </Card>
          </Col>
          <Col xs={12} sm={6}>
            <Card size="small">
              <Statistic
                title="Ready"
                value={statistics.ready || 0}
                valueStyle={{ color: '#52c41a' }}
              />
            </Card>
          </Col>
          <Col xs={12} sm={6}>
            <Card size="small">
              <Statistic
                title="Avg. Time"
                value={statistics.avg_prep_time || 0}
                suffix="min"
              />
            </Card>
          </Col>
        </Row>

        <Row gutter={16}>
          {/* Pending/Preparing Orders */}
          <Col xs={24} lg={16}>
            <Card
              title={
                <Space>
                  <FireOutlined style={{ color: '#fa8c16' }} />
                  <span>Orders In Progress ({pendingOrders.length})</span>
                </Space>
              }
              style={{ backgroundColor: '#1f1f1f', borderColor: '#303030' }}
              headStyle={{ color: '#fff', borderColor: '#303030' }}
              bodyStyle={{ maxHeight: 'calc(100vh - 280px)', overflowY: 'auto' }}
            >
              {pendingOrders.length === 0 ? (
                <Empty
                  description={<Text type="secondary">No pending orders</Text>}
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                />
              ) : (
                pendingOrders.map(order => (
                  <OrderCard key={order.order_id} order={order} showBump />
                ))
              )}
            </Card>
          </Col>

          {/* Ready Orders */}
          <Col xs={24} lg={8}>
            <Card
              title={
                <Space>
                  <CheckOutlined style={{ color: '#52c41a' }} />
                  <span>Ready for Pickup ({readyOrders.length})</span>
                </Space>
              }
              style={{ backgroundColor: '#1f1f1f', borderColor: '#303030' }}
              headStyle={{ color: '#fff', borderColor: '#303030' }}
              bodyStyle={{ maxHeight: 'calc(100vh - 280px)', overflowY: 'auto' }}
            >
              {readyOrders.length === 0 ? (
                <Empty
                  description={<Text type="secondary">No orders ready</Text>}
                  image={Empty.PRESENTED_IMAGE_SIMPLE}
                />
              ) : (
                readyOrders.map(order => (
                  <ReadyOrderCard key={order.order_id} order={order} />
                ))
              )}
            </Card>
          </Col>
        </Row>
      </div>
    </>
  )
}
