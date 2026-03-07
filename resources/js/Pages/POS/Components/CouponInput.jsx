import { useState } from 'react'
import { Input, Button, Space, Tag, message, theme } from 'antd'
import { GiftOutlined, CloseOutlined, CheckCircleOutlined } from '@ant-design/icons'

export default function CouponInput({
  couponCode,
  onApply,
  onRemove,
  isApplying,
  isRemoving,
  disabled
}) {
  const { token } = theme.useToken()
  const [inputValue, setInputValue] = useState('')

  const handleApply = () => {
    const code = inputValue.trim().toUpperCase()
    if (!code) {
      message.warning('Please enter a coupon code')
      return
    }
    onApply(code)
    setInputValue('')
  }

  const handleKeyPress = (e) => {
    if (e.key === 'Enter') {
      handleApply()
    }
  }

  // If coupon is already applied, show the applied state
  if (couponCode) {
    return (
      <div
        style={{
          display: 'flex',
          alignItems: 'center',
          justifyContent: 'space-between',
          padding: '8px 12px',
          background: token.colorSuccessBg,
          borderRadius: 6,
          border: `1px solid ${token.colorSuccessBorder}`,
        }}
      >
        <Space>
          <CheckCircleOutlined style={{ color: token.colorSuccess }} />
          <span style={{ color: token.colorSuccess, fontWeight: 500 }}>
            Coupon Applied:
          </span>
          <Tag color="green" style={{ margin: 0 }}>{couponCode}</Tag>
        </Space>
        <Button
          type="text"
          size="small"
          icon={<CloseOutlined />}
          onClick={onRemove}
          loading={isRemoving}
          style={{ color: token.colorTextSecondary }}
        />
      </div>
    )
  }

  return (
    <div style={{ marginBottom: 12 }}>
      <Space.Compact style={{ width: '100%' }}>
        <Input
          prefix={<GiftOutlined style={{ color: token.colorTextSecondary }} />}
          placeholder="Enter coupon code"
          value={inputValue}
          onChange={(e) => setInputValue(e.target.value.toUpperCase())}
          onKeyPress={handleKeyPress}
          disabled={disabled || isApplying}
          style={{ textTransform: 'uppercase' }}
        />
        <Button
          type="primary"
          onClick={handleApply}
          loading={isApplying}
          disabled={disabled || !inputValue.trim()}
        >
          Apply
        </Button>
      </Space.Compact>
    </div>
  )
}
