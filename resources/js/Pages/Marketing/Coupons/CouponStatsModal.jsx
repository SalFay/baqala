import { useEffect, useState } from 'react'
import { Statistic, Row, Col, Table, Card, Empty, Spin, Tag, Typography } from 'antd'
import { UserOutlined, ShoppingOutlined, DollarOutlined, PercentageOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { getCouponStatistics } from '@/Helpers/api/discountService'

const { Text, Title } = Typography

export default function CouponStatsModal({ visible, onCancel, record }) {
  const [loading, setLoading] = useState(true)
  const [stats, setStats] = useState(null)

  useEffect(() => {
    if (record?.id) {
      loadStats()
    }
  }, [record])

  const loadStats = async () => {
    setLoading(true)
    try {
      const response = await getCouponStatistics(record.id)
      setStats(response.data)
    } catch (error) {
      console.error('Failed to load statistics', error)
    } finally {
      setLoading(false)
    }
  }

  const usageColumns = [
    {
      title: 'Customer',
      dataIndex: 'customer',
      render: (customer) => customer?.name || <Text type="secondary">Guest</Text>,
    },
    {
      title: 'Order ID',
      dataIndex: 'order_id',
      render: (id) => id ? `#${id}` : '-',
    },
    {
      title: 'Discount Applied',
      dataIndex: 'discount_applied',
      render: (amount) => <Text strong>{amount}</Text>,
    },
    {
      title: 'Date',
      dataIndex: 'used_at',
    },
  ]

  return (
    <CustomModal
      title={
        <>
          Coupon Statistics: <Tag color="blue">{record?.code}</Tag>
        </>
      }
      open={visible}
      onCancel={onCancel}
      width={800}
      footer={null}
    >
      {loading ? (
        <div style={{ textAlign: 'center', padding: 40 }}>
          <Spin size="large" />
        </div>
      ) : stats ? (
        <>
          <Row gutter={16} style={{ marginBottom: 24 }}>
            <Col xs={24} sm={8}>
              <Card>
                <Statistic
                  title="Total Uses"
                  value={stats.total_uses}
                  prefix={<ShoppingOutlined />}
                  suffix={record?.max_uses ? `/ ${record.max_uses}` : ''}
                />
              </Card>
            </Col>
            <Col xs={24} sm={8}>
              <Card>
                <Statistic
                  title="Total Discount Given"
                  value={stats.total_discount_given}
                  prefix={<DollarOutlined />}
                  precision={2}
                />
              </Card>
            </Col>
            <Col xs={24} sm={8}>
              <Card>
                <Statistic
                  title="Unique Customers"
                  value={stats.unique_customers}
                  prefix={<UserOutlined />}
                />
              </Card>
            </Col>
          </Row>

          <Title level={5}>Recent Usage</Title>
          {stats.recent_usages?.length > 0 ? (
            <Table
              dataSource={stats.recent_usages}
              columns={usageColumns}
              rowKey="id"
              pagination={false}
              size="small"
            />
          ) : (
            <Empty description="No usage yet" />
          )}
        </>
      ) : (
        <Empty description="Failed to load statistics" />
      )}
    </CustomModal>
  )
}
