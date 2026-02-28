import { useState, useEffect } from 'react'
import { Input, Button, Typography, Space, Radio, InputNumber, theme, Divider, Tag } from 'antd'
import { PercentageOutlined, DollarOutlined, DeleteOutlined, GiftOutlined } from '@ant-design/icons'
import CustomModal from '@/Components/CustomModal'
import { formatCurrency, getCurrency } from '@/Helpers/formatters'

const { Text, Title } = Typography
const { TextArea } = Input

export default function DiscountModal({
  open,
  onClose,
  onApply,
  onRemove,
  subtotal,
  currentDiscount,
  loading,
}) {
  const { token } = theme.useToken()
  const [discountType, setDiscountType] = useState('percentage')
  const [discountValue, setDiscountValue] = useState(0)
  const [reason, setReason] = useState('')

  useEffect(() => {
    if (open) {
      if (currentDiscount?.discount_value > 0) {
        setDiscountType(currentDiscount.discount_type || 'percentage')
        setDiscountValue(currentDiscount.discount_value || 0)
        setReason(currentDiscount.discount_reason || '')
      } else {
        setDiscountType('percentage')
        setDiscountValue(0)
        setReason('')
      }
    }
  }, [open, currentDiscount])

  // Calculate preview
  const discountAmount = discountType === 'percentage'
    ? (subtotal * discountValue) / 100
    : discountValue

  const maxDiscount = discountType === 'percentage' ? 100 : subtotal

  const handleApply = () => {
    if (discountValue > 0) {
      onApply(discountValue, discountType, reason || undefined)
    }
  }

  const handleRemove = () => {
    onRemove()
    onClose()
  }

  // Quick discount buttons
  const quickDiscounts = discountType === 'percentage'
    ? [5, 10, 15, 20, 25, 50]
    : [5, 10, 20, 50, 100]

  const hasExistingDiscount = currentDiscount?.discount_value > 0

  return (
    <CustomModal
      title={
        <Space>
          <GiftOutlined style={{ color: token.colorSuccess }} />
          Apply Discount
        </Space>
      }
      open={open}
      onCancel={onClose}
      footer={null}
      width={480}
    >
      {/* Current Discount Banner */}
      {hasExistingDiscount && (
        <div
          style={{
            padding: 12,
            background: token.colorSuccessBg,
            borderRadius: 8,
            marginBottom: 20,
            display: 'flex',
            justifyContent: 'space-between',
            alignItems: 'center',
          }}
        >
          <div>
            <Text strong style={{ color: token.colorSuccess }}>
              Current Discount:
            </Text>
            <Text style={{ marginLeft: 8 }}>
              {currentDiscount.discount_type === 'percentage'
                ? `${currentDiscount.discount_value}%`
                : formatCurrency(currentDiscount.discount_value)
              }
              {' '}(-{formatCurrency(currentDiscount.discount)})
            </Text>
            {currentDiscount.discount_reason && (
              <Text type="secondary" style={{ display: 'block', fontSize: 12 }}>
                {currentDiscount.discount_reason}
              </Text>
            )}
          </div>
          <Button
            type="text"
            danger
            icon={<DeleteOutlined />}
            onClick={handleRemove}
            loading={loading}
          >
            Remove
          </Button>
        </div>
      )}

      {/* Discount Type Selection */}
      <Text strong style={{ display: 'block', marginBottom: 12 }}>Discount Type</Text>
      <Radio.Group
        value={discountType}
        onChange={(e) => {
          setDiscountType(e.target.value)
          setDiscountValue(0)
        }}
        style={{ marginBottom: 20, width: '100%' }}
      >
        <Space direction="vertical" style={{ width: '100%' }}>
          <Radio.Button
            value="percentage"
            style={{
              width: '100%',
              height: 50,
              display: 'flex',
              alignItems: 'center',
              borderRadius: 8,
            }}
          >
            <Space>
              <PercentageOutlined style={{ fontSize: 18 }} />
              <span>Percentage Discount</span>
            </Space>
          </Radio.Button>
          <Radio.Button
            value="fixed"
            style={{
              width: '100%',
              height: 50,
              display: 'flex',
              alignItems: 'center',
              borderRadius: 8,
            }}
          >
            <Space>
              <DollarOutlined style={{ fontSize: 18 }} />
              <span>Fixed Amount</span>
            </Space>
          </Radio.Button>
        </Space>
      </Radio.Group>

      {/* Discount Value */}
      <Text strong style={{ display: 'block', marginBottom: 12 }}>
        Discount {discountType === 'percentage' ? 'Percentage' : 'Amount'}
      </Text>
      <InputNumber
        size="large"
        value={discountValue}
        onChange={(val) => setDiscountValue(val || 0)}
        min={0}
        max={maxDiscount}
        addonAfter={discountType === 'percentage' ? '%' : getCurrency()}
        style={{ width: '100%', marginBottom: 12 }}
      />

      {/* Quick Discount Buttons */}
      <Space wrap style={{ marginBottom: 20 }}>
        {quickDiscounts.map((val) => (
          <Button
            key={val}
            onClick={() => setDiscountValue(val)}
            type={discountValue === val ? 'primary' : 'default'}
            size="middle"
          >
            {discountType === 'percentage' ? `${val}%` : formatCurrency(val)}
          </Button>
        ))}
      </Space>

      {/* Reason */}
      <Text strong style={{ display: 'block', marginBottom: 8 }}>
        Reason (Optional)
      </Text>
      <TextArea
        value={reason}
        onChange={(e) => setReason(e.target.value)}
        placeholder="e.g., Loyalty discount, Staff discount, Promotion..."
        rows={2}
        maxLength={255}
        style={{ marginBottom: 20 }}
      />

      <Divider style={{ margin: '16px 0' }} />

      {/* Preview */}
      <div
        style={{
          padding: 16,
          background: token.colorBgLayout,
          borderRadius: 8,
          marginBottom: 20,
        }}
      >
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Subtotal:</Text>
          <Text>{formatCurrency(subtotal)}</Text>
        </div>
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: 8 }}>
          <Text type="secondary">Discount:</Text>
          <Text style={{ color: token.colorSuccess }}>
            {discountValue > 0 ? `-${formatCurrency(discountAmount)}` : '-'}
            {discountType === 'percentage' && discountValue > 0 && (
              <Tag color="green" style={{ marginLeft: 8 }}>{discountValue}%</Tag>
            )}
          </Text>
        </div>
        <Divider style={{ margin: '8px 0' }} />
        <div style={{ display: 'flex', justifyContent: 'space-between' }}>
          <Text strong>New Subtotal:</Text>
          <Title level={4} style={{ margin: 0, color: token.colorPrimary }}>
            {formatCurrency(Math.max(0, subtotal - discountAmount))}
          </Title>
        </div>
      </div>

      {/* Actions */}
      <Space style={{ width: '100%' }} direction="vertical">
        <Button
          type="primary"
          size="large"
          block
          onClick={handleApply}
          loading={loading}
          disabled={discountValue <= 0}
        >
          Apply Discount
        </Button>
        <Button size="large" block onClick={onClose}>
          Cancel
        </Button>
      </Space>
    </CustomModal>
  )
}
