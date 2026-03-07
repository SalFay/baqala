import { useQuery } from '@tanstack/react-query'
import { Tag, Space, Typography, theme } from 'antd'
import { ThunderboltOutlined, ClockCircleOutlined, PercentageOutlined } from '@ant-design/icons'
import axios from 'axios'

const { Text } = Typography

export default function ActivePromotionsBanner() {
  const { token } = theme.useToken()

  const { data: promotions = [] } = useQuery({
    queryKey: ['active-time-pricing'],
    queryFn: async () => {
      const res = await axios.get(route('pos.time-pricing.active'))
      return res.data?.data || []
    },
    refetchInterval: 60000, // Refresh every minute
    staleTime: 30000,
  })

  if (promotions.length === 0) return null

  return (
    <div
      style={{
        background: `linear-gradient(135deg, ${token.colorPrimaryBg} 0%, ${token.colorWarningBg} 100%)`,
        padding: '8px 16px',
        borderRadius: 8,
        marginBottom: 12,
        border: `1px solid ${token.colorPrimaryBorder}`,
      }}
    >
      <Space wrap size={[8, 4]}>
        <ThunderboltOutlined style={{ color: token.colorWarning, fontSize: 16 }} />
        <Text strong style={{ color: token.colorPrimary }}>Active Now:</Text>
        {promotions.map((promo) => (
          <Tag
            key={promo.id}
            color="gold"
            icon={<PercentageOutlined />}
            style={{ margin: 0 }}
          >
            {promo.name}
            {promo.discount_type === 'percentage' && ` - ${promo.discount_value}% off`}
            {promo.discount_type === 'fixed' && ` - ${promo.discount_value} off`}
          </Tag>
        ))}
      </Space>
    </div>
  )
}
