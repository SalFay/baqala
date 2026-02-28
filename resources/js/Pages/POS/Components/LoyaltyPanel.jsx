import { useEffect } from 'react'
import { Card, Typography, Space, Tag, Spin, Button, Tooltip, theme } from 'antd'
import { GiftOutlined, StarOutlined, TrophyOutlined, WalletOutlined } from '@ant-design/icons'
import { useQuery } from '@tanstack/react-query'
import posService from '@/Helpers/api/posService'
import { formatCurrency } from '@/Helpers/formatters'

const { Text, Title } = Typography

export default function LoyaltyPanel({ customerId, cartTotal, onRedeemPoints }) {
  const { token } = theme.useToken()

  const { data: loyalty, isLoading, refetch } = useQuery({
    queryKey: ['customer-loyalty', customerId],
    queryFn: async () => {
      const response = await posService.getCustomerLoyalty(customerId)
      return response.data
    },
    enabled: !!customerId,
  })

  // Refetch when cart total changes (to update max redeemable)
  useEffect(() => {
    if (customerId) {
      refetch()
    }
  }, [cartTotal, customerId, refetch])

  if (!customerId) {
    return null
  }

  if (isLoading) {
    return (
      <Card size="small" style={{ marginBottom: 8 }}>
        <div style={{ textAlign: 'center', padding: 12 }}>
          <Spin size="small" />
          <Text type="secondary" style={{ display: 'block', marginTop: 8 }}>Loading loyalty info...</Text>
        </div>
      </Card>
    )
  }

  if (!loyalty?.has_loyalty) {
    return (
      <Card
        size="small"
        style={{
          marginBottom: 8,
          background: token.colorBgLayout,
          border: `1px dashed ${token.colorBorder}`,
        }}
      >
        <Space>
          <GiftOutlined style={{ fontSize: 20, color: token.colorTextSecondary }} />
          <div>
            <Text type="secondary">No loyalty membership yet</Text>
            <br />
            <Text style={{ fontSize: 12 }} type="secondary">
              Points will be earned after this purchase
            </Text>
          </div>
        </Space>
      </Card>
    )
  }

  const { points_balance, points_to_earn, max_redeemable, point_value, tier } = loyalty
  const redeemValue = max_redeemable * point_value

  return (
    <Card
      size="small"
      style={{
        marginBottom: 8,
        background: tier?.badge_color ? `${tier.badge_color}10` : token.colorSuccessBg,
        border: `1px solid ${tier?.badge_color || token.colorSuccess}`,
      }}
    >
      {/* Header with tier badge */}
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8 }}>
        <Space>
          <TrophyOutlined style={{ color: tier?.badge_color || token.colorSuccess }} />
          <Text strong style={{ color: tier?.badge_color || token.colorSuccess }}>
            {tier?.name || 'Loyalty Member'}
          </Text>
        </Space>
        {tier?.discount_percentage > 0 && (
          <Tag color={tier.badge_color || 'green'}>
            {tier.discount_percentage}% Member Discount
          </Tag>
        )}
      </div>

      {/* Points balance */}
      <div
        style={{
          display: 'flex',
          justifyContent: 'space-between',
          alignItems: 'center',
          padding: '8px 12px',
          background: token.colorBgContainer,
          borderRadius: 8,
          marginBottom: 8,
        }}
      >
        <div>
          <Text type="secondary" style={{ fontSize: 12 }}>Current Points</Text>
          <Title level={4} style={{ margin: 0, color: token.colorPrimary }}>
            <StarOutlined style={{ marginRight: 4 }} />
            {points_balance.toLocaleString()}
          </Title>
        </div>
        <div style={{ textAlign: 'right' }}>
          <Text type="secondary" style={{ fontSize: 12 }}>Value</Text>
          <Text strong style={{ display: 'block' }}>
            {formatCurrency(points_balance * point_value)}
          </Text>
        </div>
      </div>

      {/* Points to earn on this sale */}
      {points_to_earn > 0 && (
        <div
          style={{
            display: 'flex',
            alignItems: 'center',
            gap: 8,
            marginBottom: 8,
            padding: '4px 8px',
            background: token.colorWarningBg,
            borderRadius: 6,
          }}
        >
          <GiftOutlined style={{ color: token.colorWarning }} />
          <Text style={{ fontSize: 12 }}>
            Earns <Text strong style={{ color: token.colorWarning }}>{points_to_earn}</Text> points on this sale
          </Text>
        </div>
      )}

      {/* Redeem button */}
      {max_redeemable > 0 && (
        <Tooltip title={`Redeem up to ${max_redeemable} points (${formatCurrency(redeemValue)})`}>
          <Button
            type="primary"
            icon={<WalletOutlined />}
            onClick={() => onRedeemPoints?.(max_redeemable)}
            style={{ width: '100%' }}
            size="small"
          >
            Redeem Points ({formatCurrency(redeemValue)} off)
          </Button>
        </Tooltip>
      )}
    </Card>
  )
}
